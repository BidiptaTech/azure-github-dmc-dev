{{-- Styles for enquiry pro footer markup/discount (single + multi city). Include inside a <style> block. --}}
    .enquiry-md-panel {
        background: #ffffff;
        border: 1px solid #d7dde5;
        border-radius: 5px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        overflow: hidden;
        max-width: min(100%, 640px);
    }
    .enquiry-md-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        padding: 4px 8px;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
    }
    .enquiry-md-panel__head:hover {
        background: #eef2f7;
    }
    .enquiry-md-panel.is-collapsed .enquiry-md-panel__head {
        border-bottom: none;
    }
    .enquiry-md-panel__head-left {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }
    .enquiry-md-panel__chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        color: #64748b;
        font-size: 12px;
        line-height: 1;
        transition: transform 0.18s ease;
        flex-shrink: 0;
    }
    .enquiry-md-panel.is-collapsed .enquiry-md-panel__chevron {
        transform: rotate(-90deg);
    }
    .enquiry-md-panel__title {
        margin: 0;
        font-size: 10px;
        font-weight: 650;
        letter-spacing: 0.02em;
        color: #1e293b;
        text-transform: uppercase;
    }
    .enquiry-md-panel__hint {
        margin: 0;
        font-size: 9px;
        color: #64748b;
        white-space: nowrap;
    }
    .enquiry-md-panel__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: 700;
        color: #334155;
        background: #e2e8f0;
    }
    .enquiry-md-panel__body {
        display: block;
    }
    .enquiry-md-panel.is-collapsed .enquiry-md-panel__body {
        display: none;
    }
    .enquiry-md-single {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 10px;
        padding: 5px 8px;
    }
    .enquiry-md-field {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 2px 6px;
    }
    .enquiry-md-field--markup {
        border-left: 2px solid #16a34a;
    }
    .enquiry-md-field--discount {
        border-left: 2px solid #dc2626;
    }
    .enquiry-md-field__label {
        margin: 0;
        font-size: 9px;
        font-weight: 650;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        white-space: nowrap;
    }
    .enquiry-md-field--markup .enquiry-md-field__label { color: #15803d; }
    .enquiry-md-field--discount .enquiry-md-field__label { color: #b91c1c; }
    .enquiry-md-control,
    .enquiry-md-panel select.enquiry-md-control,
    .enquiry-md-panel input.enquiry-md-control,
    #enquiryProMarkupSingleWrap select,
    #enquiryProMarkupSingleWrap input[type="number"],
    #enquiryProMarkupMultiWrap select.city-markup-type,
    #enquiryProMarkupMultiWrap select.city-discount-type,
    #enquiryProMarkupMultiWrap input.city-markup-value,
    #enquiryProMarkupMultiWrap input.city-discount-value {
        height: 22px;
        min-height: 22px;
        font-size: 10px !important;
        line-height: 1.15;
        border: 1px solid #cbd5e1;
        border-radius: 3px;
        background: #fff;
        color: #0f172a;
        padding: 1px 5px;
        box-sizing: border-box;
    }
    #enquiryProMarkupSingleWrap select,
    #enquiryProMarkupMultiWrap select.city-markup-type,
    #enquiryProMarkupMultiWrap select.city-discount-type {
        min-width: 58px;
        width: 58px;
        cursor: pointer;
    }
    #enquiryProMarkupSingleWrap input[type="number"],
    #enquiryProMarkupMultiWrap input.city-markup-value,
    #enquiryProMarkupMultiWrap input.city-discount-value {
        width: 52px;
        text-align: right;
    }
    #enquiryProMarkupSingleWrap select:focus,
    #enquiryProMarkupSingleWrap input:focus,
    #enquiryProMarkupMultiWrap select:focus,
    #enquiryProMarkupMultiWrap input:focus {
        outline: none;
        border-color: #64748b;
        box-shadow: 0 0 0 2px rgba(100, 116, 139, 0.15);
    }
    #enquiryProMarkupSingleWrap input:disabled,
    #enquiryProMarkupMultiWrap input:disabled,
    #enquiryProMarkupSingleWrap select:disabled,
    #enquiryProMarkupMultiWrap select:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .enquiry-md-table-wrap {
        overflow-x: auto;
        max-width: 100%;
        max-height: 120px;
        overflow-y: auto;
    }
    .enquiry-md-table {
        width: 100%;
        min-width: 480px;
        margin: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        font-size: 10px;
    }
    .enquiry-md-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        color: #475569;
        font-size: 9px;
        font-weight: 650;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 4px 6px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
        vertical-align: middle;
    }
    .enquiry-md-table thead th.enquiry-md-th-markup {
        color: #15803d;
        background: #f0fdf4;
        border-bottom-color: #bbf7d0;
    }
    .enquiry-md-table thead th.enquiry-md-th-discount {
        color: #b91c1c;
        background: #fef2f2;
        border-bottom-color: #fecaca;
    }
    .enquiry-md-table tbody td {
        padding: 3px 6px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
    }
    .enquiry-md-table tbody tr:last-child td {
        border-bottom: none;
    }
    .enquiry-md-table tbody tr:hover td {
        background: #f8fafc;
    }
    .enquiry-md-city {
        display: flex;
        flex-direction: column;
        gap: 0;
        min-width: 90px;
        line-height: 1.15;
    }
    .enquiry-md-city__name {
        font-size: 10px;
        font-weight: 650;
        color: #0f172a;
    }
    .enquiry-md-city__meta {
        font-size: 8px;
        color: #64748b;
    }
    .enquiry-md-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.03em;
        color: #1e3a5f;
        background: #e8eef6;
        border: 1px solid #c5d4e8;
    }
    .enquiry-md-table td.enquiry-md-cell-markup {
        background: #fbfefc;
    }
    .enquiry-md-table td.enquiry-md-cell-discount {
        background: #fffbfb;
    }
    .enquiry-md-table tbody tr:hover td.enquiry-md-cell-markup {
        background: #f0fdf4;
    }
    .enquiry-md-table tbody tr:hover td.enquiry-md-cell-discount {
        background: #fef2f2;
    }
    input.city-discount-value.is-foc-locked,
    #discountValue.is-foc-locked {
        background: #fffbeb !important;
        border-color: #f59e0b !important;
        color: #92400e !important;
    }
