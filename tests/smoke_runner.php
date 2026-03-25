<?php

declare(strict_types=1);

/**
 * Basic end-to-end smoke checks for APNM6.
 *
 * Usage:
 *   php tests/smoke_runner.php [base_url]
 *
 * Env overrides:
 *   APNM6_SMOKE_BASE_URL
 *   APNM6_SMOKE_ADMIN_EMAIL
 *   APNM6_SMOKE_ADMIN_PASSWORD
 */

if (!extension_loaded('curl')) {
    fwrite(STDERR, "[FAIL] PHP cURL extension is required for smoke_runner.php\n");
    exit(1);
}

$baseUrl = trim((string) ($argv[1] ?? getenv('APNM6_SMOKE_BASE_URL') ?: 'http://localhost/APNM6/public'));
$baseUrl = rtrim($baseUrl, '/');
$adminEmail = (string) (getenv('APNM6_SMOKE_ADMIN_EMAIL') ?: 'director@nmims.edu');
$adminPassword = (string) (getenv('APNM6_SMOKE_ADMIN_PASSWORD') ?: 'password');

$cookieFile = tempnam(sys_get_temp_dir(), 'apnm6_smoke_cookie_');
if ($cookieFile === false) {
    fwrite(STDERR, "[FAIL] Could not create temp cookie file\n");
    exit(1);
}

register_shutdown_function(static function () use ($cookieFile): void {
    if (is_file($cookieFile)) {
        @unlink($cookieFile);
    }
});

$passCount = 0;

function pass(string $message): void
{
    global $passCount;
    $passCount++;
    fwrite(STDOUT, "[OK] {$message}\n");
}

function fail(string $message): void
{
    fwrite(STDERR, "[FAIL] {$message}\n");
    exit(1);
}

/**
 * @return array{status:int,headers:string,body:string}
 */
function request(
    string $method,
    string $url,
    string $cookieFile,
    ?array $fields = null,
    bool $followRedirects = false
): array {
    $ch = curl_init();
    if ($ch === false) {
        fail('Unable to initialize cURL');
    }

    $headers = ['Accept: text/html,application/json;q=0.9,*/*;q=0.8'];

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_FOLLOWLOCATION => $followRedirects,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields ?? []));
    }

    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        fail('Request failed for ' . $url . ': ' . $error);
    }

    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headerText = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    curl_close($ch);

    return [
        'status' => $status,
        'headers' => (string) $headerText,
        'body' => (string) $body,
    ];
}

function extractCsrfToken(string $html): ?string
{
    if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/i', $html, $matches) === 1) {
        return $matches[1];
    }

    return null;
}

function extractFirstSchoolId(string $html): ?int
{
    if (preg_match_all('/<option\s+value=["\'](\d+)["\'][^>]*>/i', $html, $matches) >= 1) {
        foreach ($matches[1] as $candidate) {
            $value = (int) $candidate;
            if ($value > 0) {
                return $value;
            }
        }
    }

    return null;
}

function getLocationHeader(string $headers): string
{
    if (preg_match('/^Location:\s*(.+)$/mi', $headers, $matches) === 1) {
        return trim($matches[1]);
    }

    return '';
}

fwrite(STDOUT, "Running APNM6 smoke checks against {$baseUrl}\n");

$index = request('GET', $baseUrl . '/index.php', $cookieFile);
if ($index['status'] !== 200) {
    fail('Public index page did not return 200 (got ' . $index['status'] . ')');
}
pass('Public index is reachable');

$submitToken = extractCsrfToken($index['body']);
if ($submitToken === null || $submitToken === '') {
    fail('Could not extract complaint submit CSRF token');
}

$schoolId = extractFirstSchoolId($index['body']);
if ($schoolId === null) {
    fail('Could not determine school_id from index form');
}

$submitPayload = [
    'csrf_token' => $submitToken,
    'school' => (string) $schoolId,
    'complaintType' => 'academic',
    'academicSubType' => 'other',
    'escalation' => 'programChair',
    'complaintDetails' => 'Automated smoke complaint at ' . date('c'),
    'anonymousCheck' => 'on',
];

$nonAnonymousInvalidPayload = [
    'csrf_token' => $submitToken,
    'school' => (string) $schoolId,
    'complaintType' => 'academic',
    'academicSubType' => 'other',
    'escalation' => 'programChair',
    'complaintDetails' => 'Non-anonymous validation check at ' . date('c'),
    // anonymousCheck intentionally omitted, identity fields intentionally omitted
];

$nonAnonymousInvalid = request('POST', $baseUrl . '/submit_complaint.php', $cookieFile, $nonAnonymousInvalidPayload);
if ($nonAnonymousInvalid['status'] !== 200) {
    fail('Non-anonymous validation check did not return 200 (got ' . $nonAnonymousInvalid['status'] . ')');
}

$nonAnonymousInvalidJson = json_decode($nonAnonymousInvalid['body'], true);
if (!is_array($nonAnonymousInvalidJson) || ($nonAnonymousInvalidJson['success'] ?? true) !== false) {
    fail('Non-anonymous validation expected failure, got: ' . $nonAnonymousInvalid['body']);
}
pass('Non-anonymous submit is blocked without Name/SAP/Year');

$submit = request('POST', $baseUrl . '/submit_complaint.php', $cookieFile, $submitPayload);
if ($submit['status'] !== 200) {
    fail('Complaint submit endpoint did not return 200 (got ' . $submit['status'] . ')');
}

$submitJson = json_decode($submit['body'], true);
if (!is_array($submitJson) || !($submitJson['success'] ?? false)) {
    fail('Complaint submit response was not successful: ' . $submit['body']);
}

$referenceNumber = (string) ($submitJson['reference_number'] ?? '');
if ($referenceNumber === '') {
    fail('Complaint submit did not return a reference number');
}
pass('Anonymous complaint submit works (' . $referenceNumber . ')');

$checkStatusGet = request('GET', $baseUrl . '/check-status/check-status.php', $cookieFile);
if ($checkStatusGet['status'] !== 200) {
    fail('Check-status page did not return 200 (got ' . $checkStatusGet['status'] . ')');
}
pass('Check-status page is reachable');

$checkStatusPost = request('POST', $baseUrl . '/check-status/check-status.php', $cookieFile, [
    'reference_number' => $referenceNumber,
]);
if ($checkStatusPost['status'] !== 200) {
    fail('Check-status POST did not return 200 (got ' . $checkStatusPost['status'] . ')');
}
if (stripos($checkStatusPost['body'], $referenceNumber) === false) {
    fail('Check-status response does not include submitted reference number');
}
pass('Reference lookup in check-status works');

$loginGet = request('GET', $baseUrl . '/admin/login.php', $cookieFile);
if ($loginGet['status'] !== 200) {
    fail('Admin login page did not return 200 (got ' . $loginGet['status'] . ')');
}

$loginToken = extractCsrfToken($loginGet['body']);
if ($loginToken === null || $loginToken === '') {
    fail('Could not extract admin login CSRF token');
}

$loginPost = request('POST', $baseUrl . '/admin/login.php', $cookieFile, [
    'csrf_token' => $loginToken,
    'email' => $adminEmail,
    'password' => $adminPassword,
]);

$location = getLocationHeader($loginPost['headers']);
$loginOk = ($loginPost['status'] === 302 && stripos($location, 'dashboard.php') !== false)
    || ($loginPost['status'] === 200 && stripos($loginPost['body'], 'Your Complaints') !== false);

if (!$loginOk) {
    fail('Admin login failed for ' . $adminEmail . '. Response status: ' . $loginPost['status']);
}
pass('Admin login works for smoke account');

$dashboard = request('GET', $baseUrl . '/admin/dashboard.php', $cookieFile);
if ($dashboard['status'] !== 200) {
    fail('Admin dashboard did not return 200 (got ' . $dashboard['status'] . ')');
}
if (stripos($dashboard['body'], 'Your Complaints') === false) {
    fail('Admin dashboard page missing expected content');
}
pass('Admin dashboard is reachable after login');

$dashboardCsv = request('GET', $baseUrl . '/admin/dashboard.php?export=csv', $cookieFile);
if ($dashboardCsv['status'] !== 200) {
    fail('Admin CSV export did not return 200 (got ' . $dashboardCsv['status'] . ')');
}
if (stripos($dashboardCsv['headers'], 'text/csv') === false) {
    fail('Admin CSV export missing CSV content type header');
}
if (stripos($dashboardCsv['body'], 'Reference Number') === false) {
    fail('Admin CSV export missing expected header row');
}
pass('Admin dashboard CSV export works');

fwrite(STDOUT, "Smoke runner completed: {$passCount} checks passed.\n");
exit(0);
