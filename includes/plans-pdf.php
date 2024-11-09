<?php
require_once 'lib/FPDF-master/fpdf.php'; // Get our FPDF lib

// Define FPDF class extension for custom layout
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Select Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, 'Bible Reading Plan', 0, 1, 'C');
        // Line break
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Table layout for plan details
    function PlanDetails($title, $startDate, $endDate, $duration)
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, $title, 0, 1, 'L');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, "Start Date: " . $startDate, 0, 1);
        $this->Cell(0, 10, "End Date: " . $endDate, 0, 1);
        $this->Cell(0, 10, "Duration: " . $duration . " days", 0, 1);
        $this->Ln(10);
    }

    function PlanList($planList)
    {
        $currentMonth = '';
        foreach ($planList as $day => $entry) {
            if ($entry['month'] . " " . $entry['year'] !== $currentMonth) {
                $currentMonth = $entry['month'] . " " . $entry['year'];
                
                if ($day !== array_key_first($planList)) {
                    $this->Ln(5);
                }
                
              $this->SetFont('Arial', 'B', 16);
                $this->Cell(0, 10, $currentMonth, 0, 1, 'C');
                $this->Ln(2);
            }
            
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 8, "Day $day: " . $entry['date'] . " - " . implode(', ', $entry['readings']), 0, 1);
            $this->Ln(2);
        }
    }

}

// Initialize the PDF
$pdf = new PDF();
$pdf->AddPage();

// Check if POST data is present
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receive the POST data
    $planName = isset($_POST['planName']) ? htmlspecialchars($_POST['planName']) : 'Bible Reading Plan';
    $startDate = isset($_POST['startDate']) ? htmlspecialchars($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? htmlspecialchars($_POST['endDate']) : '';
    $duration = isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : '';
    $testaments = isset($_POST['testaments']) ? htmlspecialchars($_POST['testaments']) : '';
    $planDetails = isset($_POST['planDetails']) ? $_POST['planDetails'] : '';

    // Decode the plan list
    $planListHTML = html_entity_decode($planDetails);
    
    // Start processing the PDF content
    $pdf->PlanDetails($planName, $startDate, $endDate, $duration);
 
    // Convert the plan list HTML to an array and format for PDF
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 8, strip_tags($planListHTML)); // Basic conversion of HTML to text

    // Output the PDF for download
    $pdf->Output('I', $planName . '_Bible_Reading_Plan.pdf');
} else {
    echo "Invalid request. Please submit the form again.";
}
?>
