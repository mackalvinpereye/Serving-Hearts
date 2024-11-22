<?php
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

require_once('../USER-VERIFICATION/config/db.php');
require '../vendor/autoload.php'; // Include PhpSpreadsheet autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch the data you want to export
$bloodInventory = $conn->query("SELECT * FROM blood_inventory ORDER BY expiration_date ASC");

if ($bloodInventory->num_rows > 0) {
    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set the header row with styles
    $header = [
        '#', 'Unique Number', 'Donor Name', 'Blood Type', 'Status', 'Request UID', 
        'Collection Date', 'Expiration Date', 'Volume (ml)', 'Remarks', 'Additives', 'Blood Component'
    ];

    $sheet->fromArray($header, NULL, 'A1');  // Set the header row
    
    // Style header row
    $sheet->getStyle('A1:L1')->getFont()->setBold(true);
    $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('D9EAD3');  // Light green background

    // Add data rows
    $rowNumber = 2; // Start from row 2 as row 1 contains headers
    while ($row = $bloodInventory->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNumber, $rowNumber - 1)
              ->setCellValue('B' . $rowNumber, $row['unique_number'])
              ->setCellValue('C' . $rowNumber, $row['fullname'])
              ->setCellValue('D' . $rowNumber, $row['blood_type'])
              ->setCellValue('E' . $rowNumber, $row['status'])
              ->setCellValue('F' . $rowNumber, $row['request_uid'])
              ->setCellValue('G' . $rowNumber, date('M d, Y', strtotime($row['collection_date'])))
              ->setCellValue('H' . $rowNumber, date('M d, Y', strtotime($row['expiration_date'])))
              ->setCellValue('I' . $rowNumber, $row['volume'])
              ->setCellValue('J' . $rowNumber, $row['remarks'])
              ->setCellValue('K' . $rowNumber, $row['additives'])
              ->setCellValue('L' . $rowNumber, $row['bloodcomponent']);
        
        // Format date columns (Collection Date, Expiration Date)
        $sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX14);
        $sheet->getStyle('H' . $rowNumber)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX14);
        
        // Format Volume column as a number with 2 decimal places
        $sheet->getStyle('I' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
        
        $rowNumber++;
    }

    // Auto size columns for better visibility
    foreach (range('A', 'L') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Add borders to all cells
    $sheet->getStyle('A1:L' . ($rowNumber - 1))
          ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Create the writer
    $writer = new Xlsx($spreadsheet);

    // Set the filename for the export
    $filename = "blood_inventory_export_" . date('Y-m-d_H-i-s') . ".xlsx";

    // Set appropriate headers to force file download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Write the file to output
    $writer->save('php://output');
    exit();
} else {
    echo "No data found to export.";
}
?>
