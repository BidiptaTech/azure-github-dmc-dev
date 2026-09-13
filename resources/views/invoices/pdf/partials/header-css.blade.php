/* Shared PDF header (used by all invoices/pdf templates) */
.header {
    width: 100%;
    margin-bottom: 12px;
}
.header-table {
    width: 100%;
    border-collapse: collapse;
}
.header-table td {
    vertical-align: middle;
    padding: 5px;
    border: none !important;
}
.header-left {
    width: 18%;
    vertical-align: top;
    text-align: left;
    padding: 0 10px 0 0;
}
.header-center {
    width: 64%;
    text-align: center;
    padding-top: 0;
}
.header-right {
    width: 18%;
    text-align: right;
    vertical-align: top;
}
.dmc-logo-wrapper {
    width: 100%;
    height: 120px;
    min-height: 120px;
    display: block;
    text-align: left;
}
.dmc-logo-wrapper img {
    max-width: 130px;
    max-height: 130px;
    display: block;
    margin-top: -16px; /* logo slightly higher than header text */
    object-fit: contain;
    object-position: left center;
}
.dmc-logo {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.header-center .dmc-name {
    font-size: 18px;
    font-weight: bold;
    margin-top: 0;
    margin-bottom: 4px;
    color: #333;
}
.header-center .dmc-address {
    font-size: 9px;
    color: #555;
    line-height: 1.45;
    margin-top: 3px;
}
.header-center .dmc-contact {
    font-size: 9px;
    color: #555;
    line-height: 1.45;
    margin-top: 2px;
}
.header-center .dmc-meta {
    font-size: 9px;
    color: #555;
    line-height: 1.45;
    margin-top: 4px;
    font-weight: normal;
}
.header-center .dmc-meta div {
    margin-top: 2px;
}
.header-right .doc-number {
    font-size: 10px;
    color: #333;
    font-weight: bold;
    padding-top: 2px;
    text-align: right;
    white-space: nowrap;
}
.header-doc-title {
    text-align: center;
    font-size: 17px;
    font-weight: bold;
    color: #2563eb;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-top: 10px;
    margin-bottom: 2px;
    font-family: Arial, Helvetica, sans-serif;
}
