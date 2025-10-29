<?php
namespace App\Helpers;

class TaxHelper
{
    /**
     * Calculate tour taxes from the taxes JSON column
     * 
     * @param float $baseAmount The base amount before tax
     * @param mixed $taxes The taxes JSON (string or array)
     * @param int $persons Number of persons (default 1)
     * @param int $days Number of days (default 1)
     * @return array ['breakdown' => ['GST' => 600, 'VAT' => 600], 'total_tax' => 1200]
     */
    public static function calculateTourTaxes($baseAmount, $taxes, $persons = 1, $days = 1)
    {
        // Initialize return array
        $result = [
            'breakdown' => [],
            'total_tax' => 0
        ];
        
        // Return early if no base amount or taxes
        if (empty($baseAmount) || empty($taxes)) {
            return $result;
        }
        
        // Decode taxes if it's a string
        if (is_string($taxes)) {
            $taxes = json_decode($taxes, true);
        }
        
        // Return early if taxes is not an array or is empty
        if (!is_array($taxes) || empty($taxes)) {
            return $result;
        }
        
        // Loop through each tax
        foreach ($taxes as $tax) {
            $taxAmount = 0;
            $taxName = $tax['tax_name'] ?? 'Unknown Tax';
            $taxType = strtolower($tax['tax_type'] ?? 'percentage');
            $taxValue = floatval($tax['tax_value'] ?? 0);
            $calculateOn = strtolower($tax['calculate_on'] ?? 'total');
            $ifFixed = $tax['if_fixed'] ?? null;
            
            // Calculate tax based on type
            if ($taxType === 'percentage' && $calculateOn === 'total') {
                // Percentage tax on total
                $taxAmount = ($baseAmount * $taxValue) / 100;
            } elseif ($taxType === 'fixed') {
                // Fixed tax with different calculation modes
                if ($ifFixed === null || $ifFixed === '') {
                    // If no mode specified, treat as per tour (just add the fixed amount)
                    $taxAmount = $taxValue;
                } else {
                    switch (strtolower($ifFixed)) {
                        case 'per_person':
                            $taxAmount = $taxValue * $persons;
                            break;
                        case 'per_tour':
                            $taxAmount = $taxValue;
                            break;
                        case 'per_person_per_tour':
                            $taxAmount = $taxValue * $persons;
                            break;
                        case 'per_person_per_day':
                            $taxAmount = $taxValue * $persons * $days;
                            break;
                        default:
                            // If mode not recognized, treat as per tour
                            $taxAmount = $taxValue;
                            break;
                    }
                }
            }
            
            // Apply ceiling to tax amount (round up to next whole number)
            $taxAmount = ceil($taxAmount);
            
            // Add to breakdown
            $result['breakdown'][$taxName] = $taxAmount;
            
            // Add to total tax
            $result['total_tax'] += $taxAmount;
        }
        
        return $result;
    }
    
    /**
     * Calculate days between two dates
     * 
     * @param string $checkIn Check-in date
     * @param string $checkOut Check-out date
     * @return int Number of days
     */
    public static function calculateDays($checkIn, $checkOut)
    {
        if (empty($checkIn) || empty($checkOut)) {
            return 1;
        }
        
        try {
            $checkInDate = new \DateTime($checkIn);
            $checkOutDate = new \DateTime($checkOut);
            $interval = $checkInDate->diff($checkOutDate);
            $days = $interval->days;
            
            // Ensure at least 1 day
            return max(1, $days);
        } catch (\Exception $e) {
            return 1;
        }
    }
    
    /**
     * Format tax breakdown for display
     * 
     * @param array $breakdown Tax breakdown array
     * @return string Formatted string
     */
    public static function formatTaxBreakdown($breakdown)
    {
        if (empty($breakdown)) {
            return 'No taxes applied';
        }
        
        $formatted = [];
        foreach ($breakdown as $taxName => $taxAmount) {
            $formatted[] = $taxName . ': ' . number_format($taxAmount, 2);
        }
        
        return implode(', ', $formatted);
    }
}

