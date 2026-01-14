<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Quotation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #212529;
            padding: 10px;
            background: #ffffff;
        }
        /* Quotation Page Styles */
        .quotation-page {
            page-break-after: always;
            background: #ffffff;
            padding: 10px;
            margin: 0;
            width: 100%;
        }
        /* Content wrapper for pages after quotation */
        .content-wrapper {
            padding: 0;
            background: #ffffff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        tbody {
            display: table-row-group;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            padding: 6px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        th {
            background-color: #f0f8ff;
            font-weight: bold;
            color: #2c3e50;
            page-break-after: avoid;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .invoice-info {
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #2c3e50;
            padding: 8px 0;
            border-bottom: 2px solid #34495e;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mb-2 {
            margin-bottom: 8px;
        }
        .mt-2 {
            margin-top: 8px;
        }
        .header-top {
            text-align: center;
            margin-bottom: 16px;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .dmc-logo-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .dmc-logo {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }
        .quotation-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .quotation-table td {
            border: 1px solid #e0e0e0;
            padding: 4px 6px;
            vertical-align: top;
            margin: 0;
        }
        .quotation-table tr {
            margin: 0;
        }
        .quotation-label {
            background: #a0aec0;
            font-weight: bold;
            padding: 4px 6px;
            border: 1px solid #90a0b0;
            color: #2c3e50;
        }
        .quotation-value {
            background: #ffffff;
            padding: 4px 6px;
            border: 1px solid #e0e0e0;
            color: #212529;
        }
        /* Hotel Options Excel-like Styles */
        .hotel-options-section {
            margin: 0 0 20px 0;
            padding: 0;
            width: 100%;
        }
        .hotel-option-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
            font-size: 11px;
            margin: 0 0 20px 0;
            padding: 0;
            page-break-inside: auto;
        }
        .hotel-option-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .hotel-option-table td {
            border: 1px solid #e0e0e0;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .hotel-option-header {
            background: #a0aec0;
            color: #2c3e50;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            padding: 6px;
        }
        .hotel-option-label {
            background: #f0f8ff;
            font-weight: bold;
            padding: 4px 6px;
            color: #2c3e50;
        }
        .hotel-option-value {
            background: #ffffff;
            padding: 4px 6px;
            color: #212529;
        }
        .hotel-total-row {
            background: #7f8c8d;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 6px;
        }
        .hotel-supplemental-header {
            background: #e8f5e9;
            color: #7b8fa3;
            font-weight: bold;
            text-align: center;
            padding: 6px;
            border: 1px solid #c8e6c9;
        }
        .hotel-supplemental-cell {
            background: #e8f5e9;
            color: #bacadb;
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
            border: 1px solid #c8e6c9;
        }
        .header-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            border: 1px solid #e5e7eb;
            margin-bottom: 16px;
            box-shadow: 0 8px 18px rgba(15,23,42,0.04);
        }
        .header-top {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .dmc-logo {
            max-width: 120px;
            max-height: 60px;
            object-fit: contain;
        }
        .dmc-company-name {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            flex: 1;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0ebff;
            color: #1d4ed8;
            font-weight: 600;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .header-card h1 {
            margin: 10px 0 4px;
            font-size: 26px;
            color: #0f172a;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .meta-tile {
            padding: 10px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-tile span {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .meta-tile strong {
            font-size: 14px;
            color: #0f172a;
            font-weight: 600;
        }
        .section {
            margin-bottom: 16px;
            page-break-inside: auto;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .section-header h3 {
            margin: 0;
            font-size: 17px;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
            padding: 8px 12px;
            background: linear-gradient(135deg, #e0ebff 0%, #c7d2fe 100%);
            border-radius: 8px;
            display: inline-block;
            border: 2px solid #3b82f6;
        }
        .section-header span {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 12px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .service-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            border: 2px solid #e2e8f0;
            display: flex;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(15,23,42,0.08);
            page-break-inside: avoid;
            break-inside: avoid;
            transition: all 0.2s;
        }
        .service-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        }
        .service-body {
            flex: 1;
            min-width: 0;
        }
        .service-title {
            font-size: 16px;
            color: #0f172a;
            font-weight: 700;
            margin: 0 0 3px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            letter-spacing: -0.01em;
        }
        .service-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-weight: 500;
        }
        .chip-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5px;
            margin-bottom: 8px;
        }
        .chip {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            font-weight: 500;
        }
        .notes {
            margin-top: 6px;
            font-size: 11px;
            color: #475569;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .rooms-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #fbbf24;
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.15);
        }
        .vehicle-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #10b981;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.15);
        }
        .vehicle-line {
            font-size: 14px;
            color: #064e3b;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .vehicle-meta {
            font-size: 11px;
            color: #065f46;
            margin: 0 0 5px;
            font-weight: 500;
        }
        .vehicle-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .vehicle-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #065f46;
            border: 1px solid #10b981;
            font-weight: 600;
        }
        .room-line {
            font-size: 14px;
            color: #78350f;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .bed-list {
            margin: 0;
            padding-left: 16px;
            color: #92400e;
            font-size: 11px;
            line-height: 1.6;
        }
        .bed-list li {
            margin-bottom: 4px;
            font-weight: 500;
        }
        .hotel-info-block {
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2);
        }
        .hotel-info-line {
            font-size: 15px;
            color: #1e40af;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }
        .hotel-info-meta {
            font-size: 12px;
            color: #1e3a8a;
            margin: 4px 0;
            font-weight: 500;
        }
        .hotel-time-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        .hotel-time-chip {
            padding: 5px 10px;
            border-radius: 6px;
            background: #ffffff;
            font-size: 11px;
            color: #1e40af;
            border: 2px solid #3b82f6;
            font-weight: 600;
        }
        .detail-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border: 2px solid #0ea5e9;
            box-shadow: 0 2px 4px rgba(14, 165, 233, 0.15);
        }
        .detail-line {
            font-size: 14px;
            color: #0c4a6e;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .detail-meta {
            font-size: 11px;
            color: #075985;
            margin: 0 0 4px;
        }
        .detail-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .detail-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #0c4a6e;
            border: 2px solid #0ea5e9;
            font-weight: 600;
        }
        .guide-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.15);
        }
        .guide-line {
            font-size: 14px;
            color: #78350f;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .guide-meta {
            font-size: 11px;
            color: #92400e;
            margin: 0 0 5px;
            font-weight: 500;
        }
        .guide-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .guide-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #92400e;
            border: 2px solid #f59e0b;
            font-weight: 600;
        }
        .empty-state {
            background: #fff;
            border-radius: 14px;
            padding: 28px;
            text-align: center;
            border: 1px dashed #c7d2fe;
            color: #64748b;
            font-size: 12px;
        }
        .price-summary-section {
            margin-top: 32px;
            page-break-inside: avoid;
        }
        .price-summary-header {
            background: #6c7a89;
            color: #ffffff;
            padding: 18px 20px;
            border-radius: 12px 12px 0 0;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .08em;
            border: 1px solid #5a6c7d;
            border-bottom: none;
        }
        .price-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 12px 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.1);
        }
        .price-summary-item {
            padding: 24px 16px;
            text-align: center;
            border-right: 1px solid #e0e0e0;
            background: #f0f8ff;
        }
        .price-summary-item:last-child {
            border-right: none;
        }
        .price-summary-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #2c3e50;
            margin-bottom: 12px;
            font-weight: 700;
        }
        .price-summary-value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.2;
        }
        .price-summary-currency {
            font-size: 20px;
            vertical-align: top;
            margin-right: 3px;
            color: #2c3e50;
            font-weight: 700;
        }
        /* Quotation First Page Styles - Matching Image Format */
        .quotation-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border: none;
        }
        .quotation-header-table td {
            vertical-align: middle;
            padding: 8px;
            border: none;
        }
        .quotation-header-table tr {
            border: none;
        }
        .quotation-logo-container {
            width: auto;
            display: inline-block;
            vertical-align: middle;
        }
        .quotation-logo {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
            vertical-align: middle;
            margin-right: 8px;
        }
        .quotation-company-name-inline {
            display: inline-block;
            vertical-align: middle;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .quotation-title-container {
            text-align: center;
            width: auto;
        }
        .quotation-main-title {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .quotation-booking-badge {
            background: #666cff;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            width: auto;
            min-width: 140px;
            display: inline-block;
        }
        .quotation-booking-badge-label {
            font-size: 10px;
            display: block;
            margin-bottom: 2px;
        }
        .quotation-booking-badge-value {
            font-size: 14px;
            display: block;
        }
        .quotation-company-info {
            margin-bottom: 20px;
            padding: 12px 0 8px 0;
        }
        .quotation-company-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .quotation-company-details {
            font-size: 14px;
            color: #212529;
            line-height: 1.8;
        }
        /* Modern Card-Based Panel Design - PDF-Compatible */
        .quotation-panels-container {
            display: table;
            width: calc(100% + 20px);
            border-collapse: separate;
            border-spacing: 10px;
            margin-bottom: 15px;
            table-layout: fixed;
            margin-left: -10px;
            margin-right: -10px;
        }
        .quotation-panel-wrapper {
            display: table-cell;
            vertical-align: top;
            padding: 0;
        }
        .quotation-panel-wrapper:first-child {
            width: 52%;
        }
        .quotation-panel-wrapper:last-child {
            width: 48%;
        }
        .quotation-panel {
            background: #f5f7f8;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            min-height: 300px;
        }
        .quotation-panel-header {
            background: #a0aec0;
            color: #2c3e50;
            font-weight: 600;
            padding: 14px 16px;
            font-size: 12px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 10px 10px 0 0;
        }
        .quotation-panel-content {
            padding: 14px 16px;
            background: #f5f7f8;
            flex: 1;
            border-radius: 0 0 10px 10px;
        }
        /* Left Panel - Two Column Internal Layout (Table-based for PDF compatibility) */
        .quotation-panel-content-two-col {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }
        .quotation-panel-col {
            display: table-cell;
            line-height: 1.4;
            vertical-align: top;
            padding: 0;
        }
        .quotation-panel-col:first-child {
            padding-right: 4px;
            width: 45%;
        }
        .quotation-panel-col:last-child {
            padding-left: 4px;
            width: 55%;
        }
        /* Right Panel - Single Column Layout */
        .quotation-panel-content-single-col {
            display: block;
        }
        /* Field Item Styling - Side by Side Layout */
        .quotation-field-item {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .quotation-field-item:last-child {
            margin-bottom: 0;
        }
        .quotation-field-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 400;
            line-height: 1.4;
            display: table-cell;
            vertical-align: middle;
            padding-right: 8px;
            width: auto;
            white-space: nowrap;
        }
        .quotation-field-value {
            font-size: 12px;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.4;
            display: table-cell;
            vertical-align: middle;
            width: 100%;
        }
        .quotation-field-icon {
            font-size: 12px;
            line-height: 1;
            color: #20b2aa;
            margin-right: 4px;
            vertical-align: middle;
            display: inline-block;
        }
        /* Pax Counts Group */
        .quotation-pax-group {
            display: block;
            margin-top: 12px;
        }
        .quotation-pax-item {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .quotation-pax-item:last-child {
            margin-bottom: 0;
        }
        .quotation-pax-label {
            display: table-cell;
            font-size: 12px;
            color: #64748b;
            font-weight: 400;
            line-height: 1.4;
            width: auto;
            padding-right: 8px;
            vertical-align: middle;
            white-space: nowrap;
        }
        .quotation-pax-value {
            display: table-cell;
            font-size: 12px;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.4;
            text-align: right;
            width: 100%;
            vertical-align: middle;
        }
        /* Field Row for Right Panel */
        .quotation-field-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
            padding: 0;
        }
        .quotation-field-row:last-child {
            margin-bottom: 0;
        }
        .quotation-field-row-label {
            display: table-cell;
            font-size: 12px;
            color: #0f172a;
            font-weight: 400;
            width: 140px;
            vertical-align: top;
            padding-right: 12px;
            padding-bottom: 4px;
        }
        .quotation-field-row-value {
            display: table-cell;
            font-size: 12px;
            color: #0f172a;
            font-weight: 400;
            text-align: left;
            vertical-align: top;
            padding-bottom: 4px;
        }
        .quotation-placeholder-line {
            border-bottom: 1px solid #cbd5e1;
            min-height: 18px;
            display: block;
            width: 100%;
            margin-top: 4px;
        }
        .quotation-travel-summary {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px 8px 0 0;
            margin-bottom: 16px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .quotation-travel-summary-header {
            background: #a0aec0;
            color: #2c3e50;
            font-weight: bold;
            padding: 12px;
            font-size: 12px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .quotation-travel-summary-content {
            padding: 14px 12px;
            background: #ffffff;
        }
        .quotation-travel-items {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .quotation-travel-item {
            display: table-cell;
            padding: 4px 4px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            vertical-align: middle;
        }
        .quotation-travel-item:first-child {
            width: 27.5%;
        }
        .quotation-travel-item:nth-child(2) {
            width: 45%;
        }
        .quotation-travel-item:last-child {
            width: 27.5%;
        }
        .quotation-travel-item-icon {
            font-size: 12px;
            margin-right: 6px;
            vertical-align: middle;
            display: inline-block;
            color: #20b2aa;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }
        .quotation-travel-item-icon:before {
            content: "•";
        }
        .quotation-travel-arrow {
            margin: 0 8px;
            font-size: 14px;
            vertical-align: middle;
            color: #20b2aa;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }
        
        .quotation-passenger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e0e0e0;
        }
        .quotation-passenger-table th {
            background: #a0aec0;
            color: #2c3e50;
            font-weight: bold;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #90a0b0;
            font-size: 11px;
        }
        .quotation-passenger-table td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
            font-size: 11px;
            background: #ffffff;
        }
        .quotation-passenger-table tbody tr:nth-child(even) td {
            background: #f8f9fa;
        }
        .quotation-passenger-table tbody tr:nth-child(odd) td {
            background: #ffffff;
        }
        .quotation-passenger-table td:first-child,
        .quotation-passenger-table td:nth-child(3),
        .quotation-passenger-table td:nth-child(4),
        .quotation-passenger-table td:nth-child(5) {
            text-align: center;
        }
        .quotation-passenger-table td:nth-child(2),
        .quotation-passenger-table td:nth-child(6) {
            text-align: left;
        }
    </style>
</head>
<body>
    @php
        $checkIn = $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('d M Y') : '-';
        $checkOut = $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('d M Y') : '-';
        $totalServices = collect($servicesByType ?? [])->flatten(1)->count();
    @endphp

    <!-- Quotation First Page (Matching Image Format) -->
    <div class="quotation-page">
        @php
            $dmcLogoSrc = null;
            if (!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0) {
                $dmcLogoSrc = $dmcLogo;
            } elseif (!empty($dmcLogo)) {
                try {
                    if (preg_match('/^https?:\/\//i', $dmcLogo)) {
                        $logoContent = @file_get_contents($dmcLogo);
                    } else {
                        $logoPath = public_path(ltrim($dmcLogo, '/'));
                        $logoContent = @file_get_contents($logoPath);
                    }
                    if ($logoContent) {
                        $base64 = base64_encode($logoContent);
                        $dmcLogoSrc = 'data:image/png;base64,' . $base64;
                    }
                } catch (\Exception $e) {
                    $dmcLogoSrc = null;
                }
            }
            $bookingId = $bookingDetails['booking_id'] ?? ($tour->display_id ?? ('DMC-' . ($tour->tour_id ?? 'N/A')));
            $leadGuestName = $bookingDetails['lead_guest_name'] ?? '';
            $leadGuestDate = $bookingDetails['lead_guest_date'] ?? ($checkIn ?? 'N/A');
            $proposalSentBy = $proposalDetails['proposal_sent_by'] ?? '—';
            $proposalDate = $proposalDetails['proposal_date'] ?? ($generatedAt->format('d M Y') ?? 'N/A');
            $proposalValidity = $proposalDetails['proposal_validity'] ?? 'N/A';
            $postalPinDate = $proposalDetails['postal_pin_date'] ?? ($travelDetails['travel_date_from'] ?? 'N/A');
            // Format postalPinDate with day name (e.g., "Tuesday-30/12/2025")
            if ($postalPinDate !== 'N/A') {
                try {
                    $pinDate = \Carbon\Carbon::parse($postalPinDate);
                    $postalPinDate = $pinDate->format('l-d/m/Y');
                } catch (\Exception $e) {
                    // Keep original format if parsing fails
                }
            }
            $operationsTeam = $proposalDetails['operations_team'] ?? 'Operations Team';
            $noOfAdults = $bookingDetails['no_of_adults'] ?? ($tour->adult ?? 0);
            $noOfChildren = $bookingDetails['no_of_children'] ?? ($tour->child ?? 0);
            $noOfInfants = $bookingDetails['no_of_infants'] ?? ($tour->infant ?? 0);
            $companyName = $dmcDetails['company_name'] ?? $dmcCompanyName ?? 'DMC Name';
            $companyAddress = $dmcDetails['address'] ?? ($dmcDetails['company_address'] ?? 'N/A');
            $companyTel = $dmcDetails['tel'] ?? ($dmcDetails['telephone'] ?? ($dmcDetails['phone'] ?? 'N/A'));
            $companyFax = $dmcDetails['fax'] ?? 'N/A';
            $companyEmail = $dmcDetails['email'] ?? ($dmcDetails['company_email'] ?? 'N/A');
            $destination = $travelDetails['destination'] ?? ($tour->destination ?? 'N/A');
            $travelDateFrom = $travelDetails['travel_date_from'] ?? ($checkIn ?? 'N/A');
            $travelDateTo = $travelDetails['travel_date_to'] ?? ($checkOut ?? 'N/A');
            $duration = $travelDetails['duration'] ?? ($tourDuration ?? 'N/A');
            
            // Format travel dates with day names (e.g., "Tuesday- 30/12/2025")
            $travelDateFromFormatted = 'N/A';
            $travelDateToFormatted = 'N/A';
            if ($travelDateFrom !== 'N/A') {
                try {
                    $fromDate = \Carbon\Carbon::parse($travelDateFrom);
                    $travelDateFromFormatted = $fromDate->format('l- d/m/Y');
                } catch (\Exception $e) {
                    $travelDateFromFormatted = $travelDateFrom;
                }
            }
            if ($travelDateTo !== 'N/A') {
                try {
                    $toDate = \Carbon\Carbon::parse($travelDateTo);
                    $travelDateToFormatted = $toDate->format('l- d/m/Y');
                } catch (\Exception $e) {
                    $travelDateToFormatted = $travelDateTo;
                }
            }
            
            // Calculate duration if not provided
            if ($duration === 'N/A' && $travelDateFrom !== 'N/A' && $travelDateTo !== 'N/A') {
                try {
                    $from = \Carbon\Carbon::parse($travelDateFrom);
                    $to = \Carbon\Carbon::parse($travelDateTo);
                    $nights = $from->diffInDays($to);
                    $days = $nights + 1;
                    $duration = $days . ' Days';
                } catch (\Exception $e) {
                    $duration = 'N/A';
                }
            }
        @endphp

        <!-- Header: Logo, Title, Booking ID Badge -->
        <table class="quotation-header-table" style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: auto; vertical-align: middle; padding: 8px; border: none;">
                    <div class="quotation-logo-container">
                        @if($dmcLogoSrc)
                            <img src="{{ $dmcLogoSrc }}" class="quotation-logo" />
                        @endif
                    </div>
                </td>
                <td class="quotation-title-container" style="text-align: center; vertical-align: middle; padding: 8px; border: none;">
                    <h1 class="quotation-main-title">QUOTATION & CONFIRMATION VOUCHER</h1>
                </td>
                {{-- <td style="text-align: right; vertical-align: middle; padding: 8px; width: auto;">
                    <div class="quotation-booking-badge">
                        <span class="quotation-booking-badge-label">BOOKING ID</span>
                        <span class="quotation-booking-badge-value">{{ $bookingId }}</span>
                    </div>
                </td> --}}
            </tr>
        </table>
        <div style="border-bottom: 1px solid #ddd; margin-bottom: 16px; margin-top: 8px;"></div>

        <!-- Company Contact Information -->
        <div class="quotation-company-info">
            <div class="quotation-company-name">{{ $companyName }}</div>
            <div class="quotation-company-details">
                {{ $companyAddress }}<br>
                Tel: {{ $companyTel }}<br>
                @if($companyFax !== 'N/A')Fax: {{ $companyFax }}<br>@endif
                Email: {{ $companyEmail }}
            </div>
        </div>

        <!-- Two Side-by-Side Panels - Modern Card-Based Design -->
        <div class="quotation-panels-container">
            <div class="quotation-panel-wrapper">
                <!-- Left Panel: Booking & Proposal Details - Two Column Internal Layout -->
                <div class="quotation-panel">
                    <div class="quotation-panel-header">BOOKING & PROPOSAL DETAILS</div>
                    <div class="quotation-panel-content quotation-panel-content-two-col">
                        <!-- Left Column -->
                        <div class="quotation-panel-col">
                            <!-- Booking ID -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">Booking ID:</div>
                                <div class="quotation-field-value">{{ $bookingId }}</div>
                            </div>
                            
                            <!-- Lead Guest -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">Lead Guest:</div>
                                <div class="quotation-field-value">{{ $leadGuestName ?: '' }}</div>
                            </div>
                            
                            
                            
                            <!-- Adults -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">Adults:</div>
                                <div class="quotation-field-value">{{ $noOfAdults }}</div>
                            </div>
                            
                            <!-- Children -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">Children:</div>
                                <div class="quotation-field-value">{{ $noOfChildren }}</div>
                            </div>
                            
                            <!-- Infants -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">Infants:</div>
                                <div class="quotation-field-value">{{ $noOfInfants }}</div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="quotation-panel-col">
                            <!-- Proposal Date -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">Proposal Date:</div>
                                <div class="quotation-field-value">{{ $proposalDate }}</div>
                            </div>
                            
                            <!-- Proposal Validity -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">Proposal Validity:</div>
                                <div class="quotation-field-value">{{ $proposalValidity }}</div>
                            </div>
                            
                            <!-- Proposal Sent By with Icon -->
                            <div class="quotation-field-item">
                                <div class="quotation-field-label">
                                    <span class="quotation-field-icon"></span>Proposal Sent By:
                                </div>
                                <div class="quotation-field-value">{{ $proposalSentBy }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="quotation-panel-wrapper">
                <!-- Right Panel: Travel Company / Agent - Single Column Layout -->
                <div class="quotation-panel">
                    <div class="quotation-panel-header">TRAVEL COMPANY / AGENT</div>
                    <div class="quotation-panel-content quotation-panel-content-single-col">
                        @php
                            $agentName = !empty($agentDetails) ? ($agentDetails['name'] ?? ($agentDetails['company_name'] ?? '')) : '';
                            $agentAddress = !empty($agentDetails) ? ($agentDetails['address'] ?? '') : '';
                            $contactPerson = !empty($agentDetails) ? ($agentDetails['contact_person'] ?? '') : '';
                            $agentPhone = !empty($agentDetails) ? ($agentDetails['phone'] ?? '') : '';
                            $agentEmail = !empty($agentDetails) ? ($agentDetails['email'] ?? '') : '';
                        @endphp
                        <div class="quotation-company-info">
                            @if($agentName)
                                <div class="quotation-company-name">{{ $agentName }}</div>
                            @endif
                            <div class="quotation-company-details">
                                @if($agentAddress){{ $agentAddress }}<br>@endif
                                @if($contactPerson)Contact Person: {{ $contactPerson }}<br>@endif
                                @if($agentPhone)Tel: {{ $agentPhone }}<br>@endif
                                @if($agentEmail)Email: {{ $agentEmail }}@endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Travel Summary Section -->
        <div class="quotation-travel-summary">
            <div class="quotation-travel-summary-header">TRAVEL SUMMARY</div>
            <div class="quotation-travel-summary-content">
                <div class="quotation-travel-items">
                    <div class="quotation-travel-item">
                        <span>Destination: {{ $destination }}</span>
                    </div>
                    <div class="quotation-travel-item">
                        
                        <span>{{ $travelDateFromFormatted }}</span>
                        <span class="quotation-travel-arrow"> &ndash; </span>
                        <span>{{ $travelDateToFormatted }}</span>
                    </div>
                    <div class="quotation-travel-item">
                        <span>Duration: {{ $duration }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Passenger Details Table -->
        <table class="quotation-passenger-table">
            <thead>
                <tr>
                    <th>Salutation</th>
                    <th>Name</th>
                    <th>Passenger Type</th>
                    <th>Gender</th>
                    <th>Mobile Phone</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $passengers = $bookingDetails['passengers'] ?? [];
                    if (empty($passengers) && !empty($leadGuestName)) {
                        // Create a default passenger entry from lead guest
                        $passengers = [[
                            'salutation' => $bookingDetails['salutation'] ?? 'Mr',
                            'first_name' => $bookingDetails['lead_guest_name'] ?? '',
                            'passenger_type' => $bookingDetails['passenger_type'] ?? 'Adult',
                            'gender' => $bookingDetails['gender'] ?? 'M',
                            'mobile_phone' => $bookingDetails['phone'] ?? '—',
                            'email' => $bookingDetails['email'] ?? '—'
                        ]];
                    }
                @endphp
                @if(!empty($passengers) && is_array($passengers))
                    @foreach($passengers as $passenger)
                        <tr>
                            <td>{{ $passenger['salutation'] ?? 'Mr' }}</td>
                            <td>{{ $passenger['first_name'] ?? '—' }}</td>
                            <td>{{ $passenger['passenger_type'] ?? 'Adult' }}</td>
                            <td>{{ $passenger['gender'] ?? 'M' }}</td>
                            <td>{{ $passenger['mobile_phone'] ?? ($passenger['phone'] ?? '—') }}</td>
                            <td>{{ $passenger['email'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $bookingDetails['salutation'] ?? 'Mr' }}</td>
                        <td>{{ $leadGuestName ?? '—' }}</td>
                        <td>{{ $bookingDetails['passenger_type'] ?? 'Adult' }}</td>
                        <td>{{ $bookingDetails['gender'] ?? 'M' }}</td>
                        <td>{{ $bookingDetails['phone'] ?? '—' }}</td>
                        <td>{{ $bookingDetails['email'] ?? '—' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Existing Content (Itinerary Details) -->
    <div class="content-wrapper">
    <!-- Header -->
    <div class="header">
        @php
            $dmcLogoSrc = null;
            if (!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0) {
                $dmcLogoSrc = $dmcLogo;
            } elseif (!empty($dmcLogo)) {
                try {
                    if (preg_match('/^https?:\/\//i', $dmcLogo)) {
                        $logoContent = @file_get_contents($dmcLogo);
                    } else {
                        $logoPath = public_path(ltrim($dmcLogo, '/'));
                        $logoContent = @file_get_contents($logoPath);
                    }
                    if ($logoContent) {
                        $base64 = base64_encode($logoContent);
                        $dmcLogoSrc = 'data:image/png;base64,' . $base64;
                    }
                } catch (\Exception $e) {
                    $dmcLogoSrc = null;
                }
            }
        @endphp
        
        
            </div>

    

    <!-- Hotel Options Section (Excel-like format) -->
    @if(!empty($hotelOptions) && count($hotelOptions) > 0)
        @php
            // ALL hotels belong to ONE OPTION - no grouping by option_number
            // All hotels are children of a single option
            $allHotels = $hotelOptions;
            $firstHotel = $allHotels[0] ?? null;
            $additionalHotels = array_slice($allHotels, 1); // Hotels after the first one
        @endphp
        <div class="hotel-options-section" style="width: 100%; text-align: center;">
            <table class="hotel-option-table" style="width: 90%; border-collapse: collapse; border: 2px solid #000000; font-size: 11px; margin: 0 auto 20px auto; padding: 0;">
                <!-- OPTION Header - Matching other services color scheme -->
                <tr>
                    <td class="hotel-option-label" style="background-color: #a0aec0; color: #2c3e50; text-align: center; font-weight: bold; font-size: 12px; padding: 8px; border: 1px solid #90a0b0; width: 40%;">Description / Label</td>
                    <td class="hotel-option-header" style="background-color: #a0aec0; color: #2c3e50; text-align: center; font-weight: bold; font-size: 12px; padding: 8px; border: 1px solid #90a0b0; width: 60%;">
                        OPTION 1
                    </td>
                </tr>
                <!-- First Hotel Only -->
                @if($firstHotel)
                    @php
                        // Calculate total room count for first hotel
                        $totalRooms = 0;
                        if (isset($firstHotel['no_of_rooms'])) {
                            $totalRooms = (int)($firstHotel['no_of_rooms']['single'] ?? 0) + 
                                         (int)($firstHotel['no_of_rooms']['double'] ?? 0) + 
                                         (int)($firstHotel['no_of_rooms']['triple'] ?? 0);
                        }
                    @endphp
                    <!-- Hotel Name with Room Count in brackets -->
                    <tr>
                        <td class="hotel-option-label" style="background: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #000000; color: #000000;">Hotel Name :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; color: #000000;">{{ $firstHotel['hotel_name'] ?? 'N/A' }} ({{ $totalRooms }} {{ $totalRooms == 1 ? 'room' : 'rooms' }})</td>
                    </tr>
                    <!-- Hotel Category -->
                    <tr>
                        <td class="hotel-option-label" style="background: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #000000; color: #000000;">Hotel Category :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; color: #000000;">{{ $firstHotel['hotel_category'] ?? 'N/A' }}</td>
                    </tr>
                @endif
                <!-- Room Pricing Header - Nested structure inside OPTION 1 column -->
                <tr>
                    <td class="hotel-option-label" style="background: #ffffff; padding: 6px 8px; border: 1px solid #90a0b0; color: #000000;"></td>
                    <td style="background: #ffffff; padding: 0; border: 1px solid #90a0b0;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                            <tr>
                                <td class="hotel-option-label" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px 8px; border: 1px solid #90a0b0; text-align: center; width: 20%;">Single</td>
                                <td class="hotel-option-label" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px 8px; border: 1px solid #90a0b0; text-align: center; width: 20%;">Double</td>
                                <td class="hotel-option-label" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px 8px; border: 1px solid #90a0b0; text-align: center; width: 20%;">Triple</td>
                                <td class="hotel-option-label" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px 8px; border: 1px solid #90a0b0; text-align: center; width: 20%;">Child</td>
                                <td class="hotel-option-label" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px 8px; border: 1px solid #90a0b0; text-align: center; width: 20%;">Infant</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Room Categories - Only first hotel's rooms -->
                @if($firstHotel)
                    @foreach($firstHotel['room_categories'] as $roomCategory)
                        <tr>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; color: #000000; vertical-align: top;">{{ !empty($roomCategory['name']) ? $roomCategory['name'] : 'N/A' }}</td>
                            <td style="background: #ffffff; padding: 0; border: 1px solid #000000; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                                    <tr>
                                        <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ is_numeric($roomCategory['single_price']) ? number_format($roomCategory['single_price'], 2) : '100.00' }}</td>
                                        <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ is_numeric($roomCategory['double_price']) ? number_format($roomCategory['double_price'], 2) : '150.00' }}</td>
                                        <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ (is_numeric($roomCategory['triple_price']) && floatval($roomCategory['triple_price']) > 0) ? number_format($roomCategory['triple_price'], 2) : 'N/A' }}</td>
                                        <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ (isset($roomCategory['child_price']) && is_numeric($roomCategory['child_price'])) ? number_format($roomCategory['child_price'], 2) : '10.00' }}</td>
                                        <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ (isset($roomCategory['infant_price']) && is_numeric($roomCategory['infant_price'])) ? number_format($roomCategory['infant_price'], 2) : '5.00' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                @endif
                <!-- First Total - Only first hotel totals -->
                @php
                    // Calculate totals for first hotel only
                    $optionFirstTotalSingle = floatval($firstHotel['first_total']['single'] ?? 0);
                    $optionFirstTotalDouble = floatval($firstHotel['first_total']['double'] ?? 0);
                    $optionFirstTotalTriple = floatval($firstHotel['first_total']['triple'] ?? 0);
                    $optionFirstTotalChild = floatval($firstHotel['first_total']['child'] ?? 0);
                    $optionFirstTotalInfant = floatval($firstHotel['first_total']['infant'] ?? 0);
                @endphp
                <tr>
                    <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d;">Total :</td>
                    <td style="background: #ffffff; padding: 0; border: 1px solid #5a6c7d;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                            <tr>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFirstTotalSingle, 2) }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFirstTotalDouble, 2) }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ ($optionFirstTotalTriple > 0) ? number_format($optionFirstTotalTriple, 2) : 'N/A' }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFirstTotalChild, 2) }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFirstTotalInfant, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Supplemental cost - Matching other services color scheme -->
                <tr>
                    <td class="hotel-supplemental-header" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #90a0b0;">Supplemental cost :</td>
                    <td style="background: #ffffff; padding: 0; border: 1px solid #90a0b0;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                            <tr>
                                <td class="hotel-supplemental-cell" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; text-align: center; padding: 6px 8px; border: 1px solid #90a0b0; width: 20%;">Single</td>
                                <td class="hotel-supplemental-cell" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; text-align: center; padding: 6px 8px; border: 1px solid #90a0b0; width: 20%;">Double</td>
                                <td class="hotel-supplemental-cell" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; text-align: center; padding: 6px 8px; border: 1px solid #90a0b0; width: 20%;">Triple</td>
                                <td class="hotel-supplemental-cell" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; text-align: center; padding: 6px 8px; border: 1px solid #90a0b0; width: 20%;">Child</td>
                                <td class="hotel-supplemental-cell" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; text-align: center; padding: 6px 8px; border: 1px solid #90a0b0; width: 20%;">Infant</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Supplemental cost rows - Additional hotels and supplemental costs -->
                @php
                    // Aggregate supplemental costs from first hotel
                    $optionSupplementalSingle = floatval($firstHotel['supplemental_cost']['single'] ?? 0);
                    $optionSupplementalDouble = floatval($firstHotel['supplemental_cost']['double'] ?? 0);
                    $optionSupplementalTriple = floatval($firstHotel['supplemental_cost']['triple'] ?? 0);
                    $optionSupplementalChild = floatval($firstHotel['supplemental_cost']['child'] ?? 10);
                    $optionSupplementalInfant = floatval($firstHotel['supplemental_cost']['infant'] ?? 5);
                @endphp
                @if(count($additionalHotels) > 0)
                    @foreach($additionalHotels as $hotel)
                        @php
                            $singleRooms = (int)($hotel['no_of_rooms']['single'] ?? 0);
                            $doubleRooms = (int)($hotel['no_of_rooms']['double'] ?? 0);
                            $tripleRooms = (int)($hotel['no_of_rooms']['triple'] ?? 0);
                            $totalRooms = $singleRooms + $doubleRooms + $tripleRooms;
                        @endphp
                        @foreach($hotel['room_categories'] as $roomCategory)
                            @php
                                $roomCategoryName = !empty($roomCategory['name']) ? $roomCategory['name'] : 'N/A';
                            @endphp
                            <tr>
                                <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; color: #000000; vertical-align: top;">{{ $hotel['hotel_name'] ?? 'N/A' }} - {{ $roomCategoryName }} - {{ $totalRooms }} {{ $totalRooms == 1 ? 'room' : 'rooms' }}</td>
                                <td style="background: #ffffff; padding: 0; border: 1px solid #000000; vertical-align: top;">
                                    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                                        <tr>
                                            <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ is_numeric($roomCategory['single_price']) ? number_format($roomCategory['single_price'], 2) : '0.00' }}</td>
                                            <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ is_numeric($roomCategory['double_price']) ? number_format($roomCategory['double_price'], 2) : '0.00' }}</td>
                                            <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ (is_numeric($roomCategory['triple_price']) && floatval($roomCategory['triple_price']) > 0) ? number_format($roomCategory['triple_price'], 2) : 'N/A' }}</td>
                                            <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ (isset($roomCategory['child_price']) && is_numeric($roomCategory['child_price'])) ? number_format($roomCategory['child_price'], 2) : '10.00' }}</td>
                                            <td class="hotel-option-value" style="background: #ffffff; padding: 6px 8px; border: 1px solid #000000; text-align: center; color: #000000; width: 20%; vertical-align: middle;">{{ (isset($roomCategory['infant_price']) && is_numeric($roomCategory['infant_price'])) ? number_format($roomCategory['infant_price'], 2) : '5.00' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
                <!-- Second Total (Final Total) - Including first hotel and additional hotels -->
                @php
                    // Calculate totals for additional hotels
                    $additionalHotelsTotalSingle = 0;
                    $additionalHotelsTotalDouble = 0;
                    $additionalHotelsTotalTriple = 0;
                    $additionalHotelsTotalChild = 0;
                    $additionalHotelsTotalInfant = 0;
                    foreach($additionalHotels as $hotel) {
                        $additionalHotelsTotalSingle += floatval($hotel['first_total']['single'] ?? 0);
                        $additionalHotelsTotalDouble += floatval($hotel['first_total']['double'] ?? 0);
                        $additionalHotelsTotalTriple += floatval($hotel['first_total']['triple'] ?? 0);
                        $additionalHotelsTotalChild += floatval($hotel['first_total']['child'] ?? 0);
                        $additionalHotelsTotalInfant += floatval($hotel['first_total']['infant'] ?? 0);
                        $additionalHotelsTotalSingle += floatval($hotel['supplemental_cost']['single'] ?? 0);
                        $additionalHotelsTotalDouble += floatval($hotel['supplemental_cost']['double'] ?? 0);
                        $additionalHotelsTotalTriple += floatval($hotel['supplemental_cost']['triple'] ?? 0);
                        $additionalHotelsTotalChild += floatval($hotel['supplemental_cost']['child'] ?? 0);
                        $additionalHotelsTotalInfant += floatval($hotel['supplemental_cost']['infant'] ?? 0);
                    }
                    $optionFinalTotalSingle = $optionFirstTotalSingle + $optionSupplementalSingle + $additionalHotelsTotalSingle;
                    $optionFinalTotalDouble = $optionFirstTotalDouble + $optionSupplementalDouble + $additionalHotelsTotalDouble;
                    $optionFinalTotalTriple = $optionFirstTotalTriple + $optionSupplementalTriple + $additionalHotelsTotalTriple;
                    $optionFinalTotalChild = $optionFirstTotalChild + $optionSupplementalChild + $additionalHotelsTotalChild;
                    $optionFinalTotalInfant = $optionFirstTotalInfant + $optionSupplementalInfant + $additionalHotelsTotalInfant;
                @endphp
                <tr>
                    <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d;">Total :</td>
                    <td style="background: #ffffff; padding: 0; border: 1px solid #5a6c7d;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                            <tr>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFinalTotalSingle, 2) }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFinalTotalDouble, 2) }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ ($optionFinalTotalTriple > 0) ? number_format($optionFinalTotalTriple, 2) : 'N/A' }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFinalTotalChild, 2) }}</td>
                                <td class="hotel-total-row" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #5a6c7d; width: 20%;">{{ number_format($optionFinalTotalInfant, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @if(empty($servicesByType))
        <div class="empty-state">
            No quotation items have been confirmed for this tour.
        </div>
    @else
        @foreach($servicesByType as $type => $cards)
                @php
                // Skip hotels as they are displayed separately above
                    $normalizedType = str_replace(' ', '_', strtolower($type));
                if ($normalizedType === 'hotel') {
                    continue;
                }
                    $sectionLabel = ucwords(str_replace('_', ' ', $type));
                    if ($normalizedType === 'entry_port') {
                    $sectionLabel = 'Arrival Services';
                    } elseif ($normalizedType === 'exit_port') {
                    $sectionLabel = 'Departure Services';
                } elseif ($normalizedType === 'attraction' || $normalizedType === 'attraction_package') {
                    $sectionLabel = 'Attraction Services';
                } elseif ($normalizedType === 'restaurant') {
                    $sectionLabel = 'Restaurant Services';
                } elseif ($normalizedType === 'guide') {
                    $sectionLabel = 'Guide Services';
                } elseif (in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly'])) {
                    $sectionLabel = 'Transfer Services';
                } else {
                    $sectionLabel = ucwords(str_replace('_', ' ', $type)) . ' Services';
                    }
                @endphp
            <div class="section-title">{{ $sectionLabel }}</div>
            <div style="page-break-inside: auto;">
            @if($normalizedType === 'entry_port')
                <!-- Arrival Services (Excel-like format with two-row tables) -->
                    @foreach($cards as $card)
                    @php
                        $pickup = '';
                        $dropoff = '';
                        $pickupDate = '';
                        $entryTime = '';
                        
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (strtolower($chip['label']) === 'pickup') {
                                $pickup = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'dropoff') {
                                $dropoff = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'date') {
                                $pickupDate = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'time') {
                                $entryTime = $chip['value'];
                            }
                        }
                        
                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? 'N/A';
                        // Format transfer type: remove underscores, capitalize words
                        if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        } else {
                            $transferType = $transferTypeRaw;
                        }
                        $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
                        $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
                        $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
                        $maxPassengerWithLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        $maxLuggageCapacity = 'N/A'; // Not typically stored
                        $maxPassengerWithoutLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        
                        // Port Name - typically from entrypickup (airport name)
                        $portName = $pickup ?: 'N/A';
                        
                        // Flight details - extract from entry_port_flight data
                        $flightData = $card['entry_port_flight'] ?? [];
                        $flightName = $flightData['flight_name'] ?? 'TBA';
                        $flightNo = $flightData['flight_no'] ?? 'TBA';
                        $originDepartureTime = $flightData['origin_departure_time'] ?? 'TBA';
                        $originDepartureTerminal = $flightData['origin_departure_terminal'] ?? 'TBA';
                        $destinationArrivalTime = $flightData['destination_arrival_time'] ?? ($entryTime ?: 'TBA');
                        $destinationArrivalTerminal = $flightData['destination_arrival_terminal'] ?? 'TBA';
                                @endphp
                    
                    <!-- First Table: Port of Arrival Transfer - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px; border: 1px solid #90a0b0; text-align: center; font-size: 12px;">
                                    Port of Arrival Transfer :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Port Name :</th>
                                <th style="width: 16.67%;">Transfer Type :</th>
                                <th style="width: 16.67%;">Vehicle Type / Seater :</th>
                                <th style="width: 16.67%;">Vehicle No :</th>
                                <th style="width: 16.67%;">Vehicle Brand :</th>
                                <th style="width: 16.67%;">Max Passenger capacity with luggage :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $portName }}</td>
                                <td>{{ $transferType }}</td>
                                <td>{{ $vehicleTypeSeater }}</td>
                                <td>{{ $vehicleNumber }}</td>
                                <td>{{ $vehicleBrand }}</td>
                                <td>{{ $maxPassengerWithLuggage }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Second Table: Port of Arrival Transfer - Parameters 7-8 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Max Luggage capacity :</th>
                                <th style="width: 16.67%;">Max Passenger Capacity without luggage :</th>
                                <th colspan="4" style="background-color: #ffffff;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $maxLuggageCapacity }}</td>
                                <td>{{ $maxPassengerWithoutLuggage }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Third Table: Flight Details - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 20px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px; border: 1px solid #90a0b0; text-align: center; font-size: 12px;">
                                    Flight Details :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Flight Name :</th>
                                <th style="width: 16.67%;">Flight No. :</th>
                                <th style="width: 16.67%;">Origin Departure Time :</th>
                                <th style="width: 16.67%;">Origin Departure Terminal :</th>
                                <th style="width: 16.67%;">Destination Arrival Time :</th>
                                <th style="width: 16.67%;">Destination Arrival Terminal :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $flightName }}</td>
                                <td>{{ $flightNo }}</td>
                                <td>{{ $originDepartureTime }}</td>
                                <td>{{ $originDepartureTerminal }}</td>
                                <td>{{ $destinationArrivalTime }}</td>
                                <td>{{ $destinationArrivalTerminal }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            @elseif($normalizedType === 'exit_port')
                <!-- Departure Services (Excel-like format with two-row tables) -->
                @foreach($cards as $card)
                    @php
                        $pickup = '';
                        $dropoff = '';
                        $pickupDate = '';
                        $exitTime = '';
                        
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (strtolower($chip['label']) === 'pickup') {
                                $pickup = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'dropoff') {
                                $dropoff = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'date') {
                                $pickupDate = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'time') {
                                $exitTime = $chip['value'];
                            }
                        }
                        
                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? 'N/A';
                        // Format transfer type: remove underscores, capitalize words
                        if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        } else {
                            $transferType = $transferTypeRaw;
                        }
                        $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
                        $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
                        $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
                        $maxPassengerWithLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        $maxLuggageCapacity = 'N/A'; // Not typically stored
                        $maxPassengerWithoutLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        
                        // Port Name - typically from exitdropoff (airport name)
                        $portName = $dropoff ?: 'N/A';
                        
                        // Flight details - extract from exit_port_flight data
                        $flightData = $card['exit_port_flight'] ?? [];
                        $flightName = $flightData['flight_name'] ?? 'TBA';
                        $flightNo = $flightData['flight_no'] ?? 'TBA';
                        $originDepartureTime = $flightData['origin_departure_time'] ?? ($exitTime ?: 'TBA');
                        $originDepartureTerminal = $flightData['origin_departure_terminal'] ?? 'TBA';
                        $destinationArrivalTime = $flightData['destination_arrival_time'] ?? 'TBA';
                        $destinationArrivalTerminal = $flightData['destination_arrival_terminal'] ?? 'TBA';
                                        @endphp
                    
                    <!-- First Table: Port of Departure Transfer - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px; border: 1px solid #90a0b0; text-align: center; font-size: 12px;">
                                    Port of Departure Transfer :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Port Name :</th>
                                <th style="width: 16.67%;">Transfer Type :</th>
                                <th style="width: 16.67%;">Vehicle Type / Seater :</th>
                                <th style="width: 16.67%;">Vehicle No :</th>
                                <th style="width: 16.67%;">Vehicle Brand :</th>
                                <th style="width: 16.67%;">Max Passenger capacity with luggage :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $portName }}</td>
                                <td>{{ $transferType }}</td>
                                <td>{{ $vehicleTypeSeater }}</td>
                                <td>{{ $vehicleNumber }}</td>
                                <td>{{ $vehicleBrand }}</td>
                                <td>{{ $maxPassengerWithLuggage }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Second Table: Port of Departure Transfer - Parameters 7-8 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Max Luggage capacity :</th>
                                <th style="width: 16.67%;">Max Passenger Capacity without luggage :</th>
                                <th colspan="4" style="background-color: #ffffff;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $maxLuggageCapacity }}</td>
                                <td>{{ $maxPassengerWithoutLuggage }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Third Table: Flight Details - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 20px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #5a6c7d; text-align: center; font-size: 14px;">
                                    Flight Details :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Flight Name :</th>
                                <th style="width: 16.67%;">Flight No. :</th>
                                <th style="width: 16.67%;">Origin Departure Time :</th>
                                <th style="width: 16.67%;">Origin Departure Terminal :</th>
                                <th style="width: 16.67%;">Destination Arrival Time :</th>
                                <th style="width: 16.67%;">Destination Arrival Terminal :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $flightName }}</td>
                                <td>{{ $flightNo }}</td>
                                <td>{{ $originDepartureTime }}</td>
                                <td>{{ $originDepartureTerminal }}</td>
                                <td>{{ $destinationArrivalTime }}</td>
                                <td>{{ $destinationArrivalTerminal }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            @elseif($normalizedType === 'attraction' || $normalizedType === 'attraction_package')
                <!-- Attraction Services Table -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Attraction Name</th>
                            <th>Attraction Timing</th>
                            <th>Transfer</th>
                            <th>Transfer Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $attractionData = $card['attraction'] ?? [];
                                
                                // Get Attraction Timing from visit_time
                                $attractionTiming = $attractionData['visit_time'] ?? 'N/A';
                                
                                // Get Transfer (Yes/No)
                                $transferRequired = $attractionData['transfer_required'] ?? 'N/A';
                                
                                // Get Transfer Type and format it
                                $transferTypeRaw = $attractionData['transfer_type'] ?? 'N/A';
                                if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                                    $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                                                    } else {
                                    $transferType = $transferTypeRaw;
                                                    }
                                                @endphp
                            <tr>
                                <td>{{ $card['title'] ?? 'N/A' }}</td>
                                <td>{{ $attractionTiming }}</td>
                                <td>{{ $transferRequired }}</td>
                                <td>{{ $transferType }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif($normalizedType === 'restaurant')
                <!-- Restaurant Services Table -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Restaurant Name</th>
                            <th>Meal Plan</th>
                            <th>Meal Type</th>
                            <th>Transfer</th>
                            <th>Transfer Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $restaurantData = $card['restaurant'] ?? [];
                                
                                // Get Meal Plan from meal_plan
                                $mealPlan = $restaurantData['meal_plan'] ?? 'N/A';
                                
                                // Get Meal Type
                                $mealType = $restaurantData['meal_type'] ?? 'N/A';
                                
                                // Get Transfer (Yes/No)
                                $transferRequired = $restaurantData['transfer_required'] ?? 'N/A';
                                
                                // Get Transfer Type and format it
                                $transferTypeRaw = $restaurantData['transfer_type'] ?? 'N/A';
                                if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                                    $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                                } else {
                                    $transferType = $transferTypeRaw;
                                                                }
                                                            @endphp
                            <tr>
                                <td>{{ $card['title'] ?? 'N/A' }}</td>
                                <td>{{ $mealPlan }}</td>
                                <td>{{ $mealType }}</td>
                                <td>{{ $transferRequired }}</td>
                                <td>{{ $transferType }}</td>
                            </tr>
                                                    @endforeach
                    </tbody>
                </table>
            @elseif($normalizedType === 'guide')
                <!-- Guide Services Table -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Tour Guide Name</th>
                            <th>Language Proficiency</th>
                            <th>Total Experience</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $guideData = $card['guide'] ?? [];
                                
                                // Get Tour Guide Name
                                $guideName = $guideData['guide_name'] ?? $card['title'] ?? 'N/A';
                                
                                // Get Language Proficiency
                                $languageProficiency = $guideData['language_proficiency'] ?? 'N/A';
                                
                                // Get Total Experience
                                $totalExperience = $guideData['total_experience'] ?? 'N/A';
                                @endphp
                            <tr>
                                <td>{{ $guideName }}</td>
                                <td>{{ $languageProficiency }}</td>
                                <td>{{ $totalExperience }}</td>
                            </tr>
                                                    @endforeach
                    </tbody>
                </table>
            @elseif(in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly']))
                <!-- Transfer Services Table (Point to Point / Hourly / Local Transport) -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Transfer Type</th>
                            <th>Vehicle Type / Seater</th>
                            <th>Vehicle No</th>
                            <th>Vehicle Brand</th>
                            <th>Max Passenger Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $vehicleData = $card['vehicle'] ?? [];
                                
                                // Get Transfer Type and format it (remove underscores, capitalize words)
                                $transferTypeRaw = $vehicleData['transfer_type'] ?? 'N/A';
                                if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                                    $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                                } else {
                                    $transferType = $transferTypeRaw;
                                }
                                
                                // Get Vehicle Type / Seater
                                $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
                                
                                // Get Vehicle No
                                $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
                                
                                // Get Vehicle Brand
                                $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
                                
                                // Get Max Passenger Capacity
                                $maxPassengerCapacity = $vehicleData['max_passenger_capacity'] ?? $vehicleData['seating_capacity'] ?? 'N/A';
                                                @endphp
                            <tr>
                                <td>{{ $transferType }}</td>
                                <td>{{ $vehicleTypeSeater }}</td>
                                <td>{{ $vehicleNumber }}</td>
                                <td>{{ $vehicleBrand }}</td>
                                <td>{{ $maxPassengerCapacity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <!-- Generic Services Table (fallback) -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Service Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Details</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $dateValue = '';
                                $timeValue = '';
                                foreach ($card['chips'] ?? [] as $chip) {
                                    if (strtolower($chip['label']) === 'date') {
                                        $dateValue = $chip['value'];
                                    }
                                    if (strtolower($chip['label']) === 'time') {
                                        $timeValue = $chip['value'];
                                                        }
                                                    }
                                                @endphp
                            <tr>
                                <td>{{ $card['title'] ?? 'N/A' }}</td>
                                <td>{{ $dateValue ?: 'N/A' }}</td>
                                <td>{{ $timeValue ?: 'N/A' }}</td>
                                <td>{{ $card['subtitle'] ?? 'N/A' }}</td>
                                <td>{{ $card['notes'] ?? 'N/A' }}</td>
                                <td>{{ $card['notes'] ?? 'N/A' }}</td>
                            </tr>
                                                    @endforeach
                    </tbody>
                </table>
                                            @endif
                                        </div>
                                                    @endforeach
                                            @endif

    <!-- Terms & Conditions Section -->
    <div style="margin-top: 30px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: #ffb6c1; color: #000000; font-weight: bold; padding: 10px; border: 2px solid #000000; text-align: left; font-size: 14px;">
                        Terms & Conditions :
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="background-color: #ffffff; padding: 20px; border: 2px solid #000000; min-height: 100px; vertical-align: top;">
                        {{ $termsAndConditions ?: '' }}
                    </td>
                </tr>
            </tbody>
        </table>
                                    </div>

    <!-- Important Notes/Disclaimers -->
    <div style="margin-top: 15px; margin-bottom: 15px;">
        <p style="color: #ff0000; font-size: 11px; margin: 5px 0;">
            *Please note that this is not a tour itinerary / schedule, a confirmed tour itinerary / schedule is only generated post confirmation of the tour and payment is completed.
        </p>
        <p style="color: #ff0000; font-size: 11px; margin: 5px 0;">
            *The above quotation only specifies the optionwise costs based on the tour requirements with standard exclusions & Inclusions as mentioned above.
        </p>
                            </div>

    <!-- Payment Terms Section -->
    <div style="margin-top: 20px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: #ffb6c1; color: #000000; font-weight: bold; padding: 10px; border: 2px solid #000000; text-align: left; font-size: 14px;">
                        Payment Terms :
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="background-color: #ffffff; padding: 20px; border: 2px solid #000000; min-height: 100px; vertical-align: top;">
                        @if(!empty($paymentTerms) && is_array($paymentTerms))
                            <ol style="margin-left: 20px; margin-top: 5px; padding-left: 20px;">
                                @foreach($paymentTerms as $term)
                                    <li style="margin-bottom: 5px;">{{ $term }}</li>
                    @endforeach
                            </ol>
                        @else
                            
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
                </div>

    <!-- Bank Details Section -->
    <div style="margin-top: 20px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: #ffb6c1; color: #000000; font-weight: bold; padding: 10px; border: 2px solid #000000; text-align: left; font-size: 14px;">
                        Bank Details :
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color: #f0f8ff; font-weight: bold; padding: 6px; border: 1px solid #000000; width: 40%;">Account Name :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['account_name'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Account Number :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['account_number'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Bank Address :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['bank_address'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">IFSC (For India only) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['ifsc'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">SWIFT / BIC / IBAN Code (as applicable for international) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['swift_bic_iban'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Bank Code (For Singapore) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['bank_code'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Branch Code (For Singapore) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['branch_code'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">ABA / Routing Number (For USA only) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['aba_routing'] ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
            </div>
    
            </div>
</body>
</html>

