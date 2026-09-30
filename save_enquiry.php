<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'message'=>'POST required']); exit; }

$fields = ['name','phone','email','type','service','message'];
$data = [];
foreach ($fields as $f) { $data[$f] = trim($_POST[$f] ?? ''); }
if ($data['name'] === '' || $data['phone'] === '' || $data['message'] === '') {
  http_response_code(422); echo json_encode(['ok'=>false,'message'=>'Name, phone and message are required.']); exit;
}

$file = __DIR__ . DIRECTORY_SEPARATOR . 'enquiries.csv';
$isNew = !file_exists($file) || filesize($file) === 0;
$fp = @fopen($file, 'a');
if (!$fp) { http_response_code(500); echo json_encode(['ok'=>false,'message'=>'CSV file is not writable. Give write permission to this folder.']); exit; }
if (flock($fp, LOCK_EX)) {
  if ($isNew) fputcsv($fp, ['Date & Time','Name','Phone','Email','Enquiry Type','Service/Product','Message']);
  fputcsv($fp, [date('Y-m-d H:i:s'), $data['name'], $data['phone'], $data['email'], $data['type'], $data['service'], $data['message']]);
  flock($fp, LOCK_UN);
}
fclose($fp);
echo json_encode(['ok'=>true,'message'=>'Enquiry saved successfully.']);
?>
