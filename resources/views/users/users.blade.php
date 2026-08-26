                                                                                                                          @extends('layouts.layout')
@section('title', 'Users')
@extends('layouts.datatablecss')

@section('content')
@php
  $settingsRoleIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
  $dmcRoleIds = [11, 20];
  $authRoleId = (int) (auth()->user()->role_id ?? 0);
  $authUserId = (int) (auth()->user()->userId ?? 0);
  $authUserIsPro = (int) (auth()->user()->is_pro ?? 0);
  // Same rule as Zone On / Price Hide / etc.: only Master DMC (role 10) may change settings.
  $isMasterDmcViewer = $authRoleId === 10;
  $collapseToTravclicksAndMasterDmc = $collapseToTravclicksAndMasterDmc ?? false;
  $masterDmcTeams = collect($masterDmcTeams ?? []);
@endphp
<style>
  /* —— Corporate users list (aligned with Master DMC team modal) —— */
  .users-corp-page .card {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }

  .users-corp-toolbar {
    margin: 0;
    padding: 1.1rem 1.35rem;
    background: linear-gradient(135deg, #0f2744 0%, #1a3a5c 55%, #243b53 100%);
    color: #fff;
  }

  .users-corp-toolbar .card-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 650;
    letter-spacing: -0.01em;
    color: #fff;
  }

  .users-corp-toolbar .users-corp-eyebrow {
    margin: 0 0 0.2rem;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.55);
  }

  .users-corp-toolbar .btn {
    border-radius: 8px;
    font-weight: 600;
    font-size: 12px;
  }

  .users-corp-toolbar .btn-primary {
    background: #3b82f6;
    border-color: #3b82f6;
  }

  .users-corp-toolbar .btn-warning {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #0f172a;
  }

  .datatables-basic {
    font-size: 12px;
    line-height: 1.35;
    border: 0 !important;
    margin: 0 !important;
  }

  .datatables-basic thead th {
    background: linear-gradient(135deg, #0f2744 0%, #1a3a5c 100%) !important;
    border: 0 !important;
    border-bottom: 2px solid #2d6a9f !important;
    color: #ffffff !important;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 0.95rem 1rem !important;
    white-space: nowrap;
    vertical-align: middle;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
  }

  .datatables-basic thead th:first-child {
    border-top-left-radius: 0;
  }

  .datatables-basic thead th + th {
    border-left: 1px solid rgba(255, 255, 255, 0.12) !important;
  }

  /* DataTables sorting icons remain readable on dark header */
  .datatables-basic thead th.sorting:before,
  .datatables-basic thead th.sorting:after,
  .datatables-basic thead th.sorting_asc:before,
  .datatables-basic thead th.sorting_asc:after,
  .datatables-basic thead th.sorting_desc:before,
  .datatables-basic thead th.sorting_desc:after {
    color: rgba(255, 255, 255, 0.55) !important;
    opacity: 1 !important;
  }

  .datatables-basic tbody td {
    padding: 1rem !important;
    vertical-align: top;
    border-color: #eef2f7 !important;
    background: #fff;
  }

  .datatables-basic tbody tr:hover td {
    background: #fafbfc;
  }

  .datatables-basic tr.user-inactive-row td {
    background: #fbfbfc;
    opacity: 0.92;
  }

  .datatables-basic .form-check {
    margin-bottom: 0;
    min-height: 0;
  }

  .datatables-basic .form-check-input {
    margin-top: 0;
  }

  .users-corp-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(145deg, #1e4a7a, #2d6a9f);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .users-corp-name {
    font-weight: 650;
    font-size: 14px;
    color: #0f172a;
    line-height: 1.25;
    margin: 0;
  }

  .users-corp-company {
    font-size: 12px;
    color: #64748b;
    margin-top: 0.15rem;
  }

  .users-corp-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-top: 0.45rem;
  }

  .users-corp-pill {
    display: inline-flex;
    align-items: center;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    border: 1px solid transparent;
  }

  .users-corp-pill-role {
    background: #eef2ff;
    color: #3730a3;
    border-color: #e0e7ff;
  }

  .users-corp-pill-type {
    background: #f1f5f9;
    color: #475569;
    border-color: #e2e8f0;
  }

  .users-corp-meta {
    margin-top: 0.55rem;
    display: grid;
    gap: 0.2rem;
  }

  .users-corp-meta span {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.35;
  }

  .users-corp-meta i {
    width: 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 10px;
  }

  .users-corp-section-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 0.45rem;
  }

  .users-corp-controls {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.4rem;
    min-width: 260px;
    max-width: 360px;
  }

  .users-corp-control-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.45rem;
    background: #f8fafc;
    border: 1px solid #e8eef5;
    border-radius: 8px;
    padding: 0.4rem 0.55rem;
    min-height: 36px;
  }

  .users-corp-control-item .users-corp-label {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
  }

  .users-corp-control-item .form-check {
    margin: 0;
    min-height: 0;
    padding-left: 2.5em;
    display: flex;
    align-items: center;
  }

  .users-corp-control-item .form-check.form-switch {
    padding-left: 2.6em;
  }

  .users-corp-control-item .form-check-input {
    float: none;
    cursor: pointer;
    margin-top: 0;
    margin-left: -2.6em;
  }

  /* Force clear toggle look for Zone / Price / Email / 3rd Party */
  .users-corp-control-item .form-switch .form-check-input {
    width: 2.4em !important;
    height: 1.25em !important;
    margin-left: -2.6em;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e") !important;
    background-position: left center;
    background-repeat: no-repeat;
    background-size: contain;
    background-color: #cbd5e1 !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 2em !important;
    transition: background-position 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    appearance: none;
    -webkit-appearance: none;
  }

  .users-corp-control-item .form-switch .form-check-input:checked {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
    background-position: right center;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e") !important;
  }

  .users-corp-control-item .form-switch .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.2) !important;
  }

  .users-corp-control-item .form-switch .form-check-input:disabled {
    opacity: 0.55;
    cursor: not-allowed;
  }

  .users-corp-control-item .form-select {
    height: 28px !important;
    font-size: 11px !important;
    padding: 0.15rem 0.4rem !important;
    min-width: 84px;
    width: auto;
    border-color: #dbe3ee;
    border-radius: 6px;
    background-color: #fff;
  }

  .users-corp-status {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    min-width: 76px;
  }

  .users-corp-status-badge {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.28rem 0.55rem;
    border-radius: 999px;
  }

  .users-corp-status-badge.is-active {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
  }

  .users-corp-status-badge.is-inactive {
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
  }

  .users-corp-actions {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0.4rem;
    min-width: 108px;
  }

  .users-corp-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    font-size: 11px;
    font-weight: 600;
    border-radius: 8px;
    height: 32px;
    padding: 0 0.7rem;
    white-space: nowrap;
  }

  .users-corp-actions .btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
  }

  .users-corp-actions-row {
    display: flex;
    gap: 0.35rem;
    justify-content: flex-end;
  }

  .users-corp-empty-controls {
    color: #94a3b8;
    font-size: 12px;
    font-style: italic;
  }

  .auto-login-disabled {
    cursor: not-allowed;
    opacity: 0.55;
  }

  .dataTables_wrapper {
    padding: 0 0.75rem 1rem;
    background: #f4f6f9;
  }

  .dataTables_wrapper .dataTables_filter input,
  .dataTables_wrapper .dataTables_length select {
    height: 32px;
    font-size: 12px;
    padding: 0.2rem 0.55rem;
    border-radius: 8px;
    border: 1px solid #dbe3ee;
  }

  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_paginate {
    padding-top: 0.75rem;
    color: #64748b;
    font-size: 12px;
  }

  .master-dmc-team-trigger {
    cursor: pointer;
    border: 0;
  }

  .master-dmc-team-trigger:hover {
    filter: brightness(0.95);
  }

  @media (max-width: 992px) {
    .users-corp-controls {
      grid-template-columns: 1fr;
      min-width: 0;
      max-width: none;
    }
  }

  /* —— Corporate Master DMC team modal —— */
  #masterDmcTeamModal .modal-dialog {
    max-width: 1080px;
    width: 94vw;
  }

  #masterDmcTeamModal .mdmc-corp-modal {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(15, 23, 42, 0.22);
  }

  #masterDmcTeamModal .mdmc-corp-header {
    background: linear-gradient(135deg, #0f2744 0%, #1a3a5c 55%, #243b53 100%);
    color: #fff;
    border: 0;
    padding: 1.15rem 1.35rem;
    align-items: flex-start;
  }

  #masterDmcTeamModal .mdmc-corp-eyebrow {
    margin: 0 0 0.2rem;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.55);
  }

  #masterDmcTeamModal .mdmc-corp-header .modal-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 650;
    letter-spacing: -0.01em;
    color: #fff;
  }

  #masterDmcTeamModal .mdmc-corp-subtitle {
    margin: 0.35rem 0 0;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.72);
  }

  #masterDmcTeamModal .mdmc-corp-header .btn-close {
    filter: invert(1) grayscale(1) brightness(1.8);
    opacity: 0.75;
    margin-top: 0.15rem;
  }

  #masterDmcTeamModal .mdmc-corp-header .btn-close:hover {
    opacity: 1;
  }

  #masterDmcTeamModal .mdmc-corp-body {
    background: #f4f6f9;
    padding: 1rem 1.15rem 1.25rem;
    max-height: min(68vh, 720px);
    overflow-y: auto;
  }

  #masterDmcTeamModal .mdmc-corp-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  #masterDmcTeamModal .mdmc-corp-card {
    background: #fff;
    border: 1px solid #e6ebf2;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
  }

  #masterDmcTeamModal .mdmc-corp-card:hover {
    border-color: #d5dde8;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07);
  }

  #masterDmcTeamModal .mdmc-corp-card.is-inactive {
    background: #fbfbfc;
    opacity: 0.92;
  }

  #masterDmcTeamModal .mdmc-corp-grid {
    display: grid;
    grid-template-columns: minmax(220px, 1.15fr) minmax(280px, 1.45fr) auto auto;
    gap: 1rem 1.25rem;
    align-items: start;
  }

  #masterDmcTeamModal .mdmc-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(145deg, #1e4a7a, #2d6a9f);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  #masterDmcTeamModal .mdmc-user-name {
    font-weight: 650;
    font-size: 14px;
    color: #0f172a;
    line-height: 1.25;
    margin: 0;
  }

  #masterDmcTeamModal .mdmc-user-company {
    font-size: 12px;
    color: #64748b;
    margin-top: 0.15rem;
  }

  #masterDmcTeamModal .mdmc-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-top: 0.45rem;
  }

  #masterDmcTeamModal .mdmc-pill {
    display: inline-flex;
    align-items: center;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    border: 1px solid transparent;
  }

  #masterDmcTeamModal .mdmc-pill-role {
    background: #eef2ff;
    color: #3730a3;
    border-color: #e0e7ff;
  }

  #masterDmcTeamModal .mdmc-pill-type {
    background: #f1f5f9;
    color: #475569;
    border-color: #e2e8f0;
  }

  #masterDmcTeamModal .mdmc-user-meta {
    margin-top: 0.55rem;
    display: grid;
    gap: 0.2rem;
  }

  #masterDmcTeamModal .mdmc-user-meta span {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.35;
  }

  #masterDmcTeamModal .mdmc-user-meta i {
    width: 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 10px;
  }

  #masterDmcTeamModal .mdmc-section-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 0.45rem;
  }

  #masterDmcTeamModal .mdmc-controls {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.4rem;
  }

  #masterDmcTeamModal .mdmc-control-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.45rem;
    background: #f8fafc;
    border: 1px solid #e8eef5;
    border-radius: 8px;
    padding: 0.4rem 0.55rem;
    min-height: 36px;
  }

  #masterDmcTeamModal .mdmc-control-item .mdmc-label {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
  }

  #masterDmcTeamModal .mdmc-control-item .form-check {
    margin: 0;
    min-height: 0;
    padding-left: 2.6em;
    display: flex;
    align-items: center;
  }

  #masterDmcTeamModal .mdmc-control-item .form-switch .form-check-input {
    width: 2.4em !important;
    height: 1.25em !important;
    margin-top: 0;
    margin-left: -2.6em;
    float: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e") !important;
    background-position: left center;
    background-repeat: no-repeat;
    background-size: contain;
    background-color: #cbd5e1 !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 2em !important;
    transition: background-position 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    appearance: none;
    -webkit-appearance: none;
  }

  #masterDmcTeamModal .mdmc-control-item .form-switch .form-check-input:checked {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
    background-position: right center;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e") !important;
  }

  #masterDmcTeamModal .mdmc-control-item .form-switch .form-check-input:disabled {
    opacity: 0.55;
    cursor: not-allowed;
  }

  #masterDmcTeamModal .mdmc-control-item .form-select {
    height: 28px !important;
    font-size: 11px !important;
    padding: 0.15rem 0.4rem !important;
    min-width: 84px;
    width: auto;
    border-color: #dbe3ee;
    border-radius: 6px;
    background-color: #fff;
  }

  #masterDmcTeamModal .mdmc-status-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding-top: 0.15rem;
    min-width: 76px;
  }

  #masterDmcTeamModal .mdmc-status-badge {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.28rem 0.55rem;
    border-radius: 999px;
  }

  #masterDmcTeamModal .mdmc-status-badge.is-active {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
  }

  #masterDmcTeamModal .mdmc-status-badge.is-inactive {
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
  }

  #masterDmcTeamModal .mdmc-actions {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0.4rem;
    min-width: 108px;
  }

  #masterDmcTeamModal .mdmc-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    font-size: 11px;
    font-weight: 600;
    border-radius: 8px;
    height: 32px;
    padding: 0 0.7rem;
    white-space: nowrap;
  }

  #masterDmcTeamModal .mdmc-actions .btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
  }

  #masterDmcTeamModal .mdmc-actions-row {
    display: flex;
    gap: 0.35rem;
    justify-content: flex-end;
  }

  #masterDmcTeamModal .mdmc-empty-controls {
    color: #94a3b8;
    font-size: 12px;
    font-style: italic;
  }

  #masterDmcTeamModal .mdmc-corp-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #64748b;
    background: #fff;
    border: 1px dashed #d7dee8;
    border-radius: 12px;
  }

  #masterDmcTeamModal .mdmc-corp-footer {
    background: #fff;
    border-top: 1px solid #e8eef5;
    padding: 0.85rem 1.25rem;
  }

  #masterDmcTeamModal .mdmc-corp-footer .btn {
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    padding: 0.45rem 1rem;
  }

  #masterDmcTeamModal.show,
  #deleteModal.show,
  #walletmodal.show {
    display: block !important;
    z-index: 1100;
  }

  .modal-backdrop.show {
    z-index: 1090;
  }

  @media (max-width: 900px) {
    #masterDmcTeamModal .mdmc-corp-grid {
      grid-template-columns: 1fr;
    }

    #masterDmcTeamModal .mdmc-actions,
    #masterDmcTeamModal .mdmc-status-block {
      align-items: flex-start;
    }

    #masterDmcTeamModal .mdmc-actions-row {
      justify-content: flex-start;
    }
  }

  @media (max-width: 576px) {
    #masterDmcTeamModal .mdmc-controls {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y users-corp-page">
    <div class="card">
      <div class="card-datatable table-responsive pt-0">
            <div class="d-flex justify-content-between align-items-center users-corp-toolbar">
                <div>
                    <p class="users-corp-eyebrow">User management</p>
                    <h5 class="card-title mb-0">Users</h5>
                </div>

                <div class="d-flex justify-content-between gap-2">
                    @if(hasPermission('create users'))
                      <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                          <i class="fas fa-plus"></i> Add New User
                      </a>
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <x-alert />

        <table class="datatables-basic table">
          <thead>
            <tr>
              <th>User</th>
              <th class="settings-controls-col">Settings</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $key => $user)
              @php
                $rowRoleId = (int) ($user->role_id ?? 0);
                $showSettingsForThisRow = in_array($rowRoleId, $settingsRoleIds, true)
                    || ($authRoleId === 10 && $rowRoleId === 11);
                $showThirdPartyForThisRow = in_array($rowRoleId, $dmcRoleIds, true)
                    && strtolower((string) ($user->thirdparty ?? 'no')) === 'yes';
                $canToggleThirdPartyForThisRow = $showThirdPartyForThisRow
                    && $isMasterDmcViewer
                    && (int) ($user->master_dmc_id ?? 0) === $authUserId;
                $thirdPartyEnabled = strtolower((string) ($user->thirdparty_enabled ?? 'no')) === 'yes';
                $showBookingTypeForThisRow = ($authRoleId === 1 && (int) ($user->role_id ?? 0) === 10)
                    || ($authRoleId === 10 && (int) ($user->role_id ?? 0) === 11);
                $showSettingsCellForThisRow = $showSettingsForThisRow || $showThirdPartyForThisRow || $showBookingTypeForThisRow;
                $bookingOptionsForRow = [
                    1 => 'Lite Form',
                    2 => 'Pro Form',
                    3 => 'Both',
                ];
                $selectedBookingTypeForRow = (int) ($user->is_pro ?? 1);
                $bookingTypeLockedForRow = false;

                if ($authRoleId === 10 && (int) ($user->role_id ?? 0) === 11) {
                    if ($authUserIsPro === 1) {
                        $bookingOptionsForRow = [1 => 'Lite Form'];
                        $selectedBookingTypeForRow = 1;
                        $bookingTypeLockedForRow = true;
                    } elseif ($authUserIsPro === 2) {
                        $bookingOptionsForRow = [2 => 'Pro Form'];
                        $selectedBookingTypeForRow = 2;
                        $bookingTypeLockedForRow = true;
                    } elseif ($authUserIsPro === 3) {
                        $bookingOptionsForRow = [
                            1 => 'Lite Form',
                            2 => 'Pro Form',
                            3 => 'Both',
                        ];
                        $selectedBookingTypeForRow = 3;
                    }
                } elseif (!in_array($selectedBookingTypeForRow, [1, 2, 3], true)) {
                    $selectedBookingTypeForRow = 1;
                }
                $userIsActive = (int) ($user->is_active ?? 1) === 1;
                $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if (count($nameParts) >= 2) {
                    $userInitials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1));
                } elseif (count($nameParts) === 1) {
                    $userInitials = strtoupper(mb_substr($nameParts[0], 0, 2));
                } else {
                    $userInitials = 'U';
                }
                $canEditSettings = (int) auth()->user()->role_id === 10;
                $showAutoLogin = in_array((int) auth()->user()->user_type, [1, 2, 3], true);
              @endphp
              <tr class="{{ ($showSettingsCellForThisRow ? '' : 'no-settings-row') . ($userIsActive ? '' : ' user-inactive-row') }}">
                <td>
                  <div class="d-flex align-items-start gap-3">
                    <div class="users-corp-avatar" title="#{{ $key + 1 }}">{{ $userInitials }}</div>
                    <div class="flex-grow-1 min-w-0">
                      <p class="users-corp-name">{{ $user->name }}</p>
                      <div class="users-corp-company">{{ $user->company_name ?? 'N/A' }}</div>
                      <div class="users-corp-pills">
                        @if($collapseToTravclicksAndMasterDmc && $rowRoleId === 10)
                          <button type="button"
                            class="users-corp-pill users-corp-pill-role master-dmc-team-trigger"
                            data-toggle="modal"
                            data-target="#masterDmcTeamModal"
                            data-bs-toggle="modal"
                            data-bs-target="#masterDmcTeamModal"
                            data-master-id="{{ $user->userId }}"
                            data-master-name="{{ $user->name }}"
                            title="View DMC and other roles under this Master DMC">
                            {{ $user->role->name ?? 'Master Dmc' }}
                            <i class="fas fa-users ms-1"></i>
                          </button>
                        @else
                          <span class="users-corp-pill users-corp-pill-role">{{ $user->role->name ?? 'No Role' }}</span>
                        @endif
                        <span class="users-corp-pill users-corp-pill-type">{{ $user->getUserTypeName() }}</span>
                      </div>
                      <div class="users-corp-meta">
                        <span><i class="fas fa-envelope"></i>{{ $user->email ?? '—' }}</span>
                        @if($user->phone)
                          <span><i class="fas fa-phone"></i>{{ $user->phone }}</span>
                        @endif
                        <span>
                          <i class="fas fa-map-marker-alt"></i>
                          {{ $user->user_country ?? 'N/A' }}{{ $user->city ? ', ' . $user->city : '' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </td>

                <td class="settings-controls-cell">
                  <div class="users-corp-section-label">Settings</div>
                  @if($showSettingsForThisRow || $showThirdPartyForThisRow || $showBookingTypeForThisRow)
                  <div class="users-corp-controls">
                    @if($showSettingsForThisRow)
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">Zone</span>
                        <div class="form-check form-switch mb-0">
                          <input {{ $user->zone_on == 1 ? 'checked' : '' }}
                            class="form-check-input {{ $canEditSettings ? 'zone-toggle' : '' }}"
                            data-user-id="{{ $user->userId }}"
                            type="checkbox"
                            role="switch"
                            id="zone_on_{{ $user->userId }}"
                            value="1"
                            {{ $canEditSettings ? '' : 'disabled' }}>
                        </div>
                      </div>
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">Price</span>
                        <div class="form-check form-switch mb-0">
                          <input {{ $user->price_hide == 1 ? 'checked' : '' }}
                            class="form-check-input {{ $canEditSettings ? 'price-hide_toggle' : '' }}"
                            data-user-id="{{ $user->userId }}"
                            type="checkbox"
                            role="switch"
                            id="price_hide_{{ $user->userId }}"
                            value="1"
                            {{ $canEditSettings ? '' : 'disabled' }}>
                        </div>
                      </div>
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">Email</span>
                        <div class="form-check form-switch mb-0">
                          <input {{ $user->email_on == 1 ? 'checked' : '' }}
                            class="form-check-input {{ $canEditSettings ? 'email-toggle' : '' }}"
                            data-user-id="{{ $user->userId }}"
                            type="checkbox"
                            role="switch"
                            id="email_on_{{ $user->userId }}"
                            value="1"
                            {{ $canEditSettings ? '' : 'disabled' }}>
                        </div>
                      </div>
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">Cancel</span>
                        @if($canEditSettings)
                          <div class="form-group auto-cancel-day-wrap mb-0" data-user-id="{{ $user->userId }}"
                            style="display: {{ ($user->auto_cancel_date !== null && $user->auto_cancel_date >= 1) ? 'block' : 'none' }};">
                            <select class="form-select auto-cancel-dropdown"
                              data-user-id="{{ $user->userId }}"
                              id="auto_cancel_{{ $user->userId }}">
                              @for($d = 1; $d <= 14; $d++)
                                <option value="{{ $d }}" {{ ($user->auto_cancel_date == $d || (is_null($user->auto_cancel_date) && $d === 1)) ? 'selected' : '' }}>D-{{ $d }}</option>
                              @endfor
                            </select>
                          </div>
                          @if($user->auto_cancel_date === null || $user->auto_cancel_date < 1)
                            <span class="text-muted" style="font-size:10px;">Off</span>
                          @endif
                        @else
                          <span class="text-muted" style="font-size:11px;">
                            {{ ($user->auto_cancel_date !== null && $user->auto_cancel_date >= 1) ? ('D-' . $user->auto_cancel_date) : 'Off' }}
                          </span>
                        @endif
                      </div>
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">AI</span>
                        @if($canEditSettings)
                          <select class="form-select ai-response-dropdown"
                            data-user-id="{{ $user->userId }}"
                            data-previous-value="{{ strtoupper((string) ($user->ai_response ?? '')) }}"
                            id="ai_response_{{ $user->userId }}"
                            title="QTN = Quotation, ITN = Itinerary">
                            <option value="" {{ empty($user->ai_response) ? 'selected' : '' }}>--</option>
                            <option value="QTN" {{ strtoupper((string) ($user->ai_response ?? '')) === 'QTN' ? 'selected' : '' }}>QTN</option>
                            <option value="ITN" {{ strtoupper((string) ($user->ai_response ?? '')) === 'ITN' ? 'selected' : '' }}>ITN</option>
                          </select>
                        @else
                          <span class="text-muted" style="font-size:11px;">{{ $user->ai_response ?: '—' }}</span>
                        @endif
                      </div>
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">AI Resp</span>
                        @if($canEditSettings)
                          <select class="form-select ai-response-type-dropdown"
                            data-user-id="{{ $user->userId }}"
                            data-previous-value="{{ strtolower((string) ($user->ai_response_type ?? '')) }}"
                            id="ai_response_type_{{ $user->userId }}"
                            title="AI Response Type: Lite or Pro">
                            <option value="" {{ empty($user->ai_response_type) ? 'selected' : '' }}>--</option>
                            <option value="lite" {{ strtolower((string) ($user->ai_response_type ?? '')) === 'lite' ? 'selected' : '' }}>Lite</option>
                            <option value="pro" {{ strtolower((string) ($user->ai_response_type ?? '')) === 'pro' ? 'selected' : '' }}>Pro</option>
                          </select>
                        @else
                          <span class="text-muted" style="font-size:11px;">{{ $user->ai_response_type ? ucfirst($user->ai_response_type) : '—' }}</span>
                        @endif
                      </div>
                    @endif

                    @if($showThirdPartyForThisRow)
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">3rd Party</span>
                        <div class="form-check form-switch mb-0">
                          <input {{ $thirdPartyEnabled ? 'checked' : '' }}
                            class="form-check-input {{ $canToggleThirdPartyForThisRow ? 'third-party-toggle' : '' }}"
                            data-user-id="{{ $user->userId }}"
                            type="checkbox"
                            role="switch"
                            id="thirdparty_enabled_{{ $user->userId }}"
                            value="1"
                            title="{{ $canToggleThirdPartyForThisRow
                                ? ($thirdPartyEnabled ? 'Third party access on' : 'Third party access off')
                                : 'Only the Master DMC can change third party access' }}"
                            {{ $canToggleThirdPartyForThisRow ? '' : 'disabled' }}>
                        </div>
                      </div>
                    @endif

                    @if($showBookingTypeForThisRow)
                      <div class="users-corp-control-item">
                        <span class="users-corp-label">Booking</span>
                        <select class="form-select booking-type-select"
                          data-user-id="{{ $user->userId }}"
                          data-previous-value="{{ $selectedBookingTypeForRow }}"
                          {{ $bookingTypeLockedForRow ? 'disabled' : '' }}>
                          @foreach($bookingOptionsForRow as $bookingValue => $bookingLabel)
                            <option value="{{ $bookingValue }}" {{ $selectedBookingTypeForRow === $bookingValue ? 'selected' : '' }}>
                              {{ $bookingLabel }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    @endif
                  </div>
                  @else
                    <span class="users-corp-empty-controls">No settings for this role</span>
                  @endif
                </td>

                <td>
                  <div class="users-corp-section-label text-center">Status</div>
                  <div class="users-corp-status">
                    @if(hasPermission('edit users'))
                      <div class="form-check form-switch mb-0">
                        <input type="checkbox"
                          class="form-check-input user-active-toggle"
                          data-user-id="{{ $user->userId }}"
                          id="user_active_{{ $user->userId }}"
                          {{ $userIsActive ? 'checked' : '' }}
                          {{ (int) $user->userId === (int) auth()->user()->userId ? 'data-self="1"' : '' }}
                          style="width: 36px; height: 18px; cursor: pointer;"
                          title="{{ $userIsActive ? 'Active — click to deactivate' : 'Inactive — click to activate' }}">
                      </div>
                    @endif
                    <span class="users-corp-status-badge {{ $userIsActive ? 'is-active' : 'is-inactive' }}">
                      {{ $userIsActive ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </td>

                <td>
                  <div class="users-corp-section-label text-end">Actions</div>
                  <div class="users-corp-actions">
                    @if($showAutoLogin)
                      @if($userIsActive)
                        <a href="{{ route('admin.loginAsUser', $user->userId) }}"
                           class="btn btn-primary"
                           title="Auto login as {{ $user->name }}">
                          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg>
                          Login
                        </a>
                      @else
                        <button type="button" class="btn btn-secondary auto-login-disabled" disabled title="Auto login unavailable — user is inactive">Login</button>
                      @endif
                    @endif

                    @if(hasPermission('edit users') || hasPermission('delete users'))
                      <div class="users-corp-actions-row">
                        @if(hasPermission('edit users'))
                          <a href="{{ route('users.edit', Crypt::encrypt($user->userId)) }}"
                            class="btn btn-outline-primary btn-icon"
                            title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor"><path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/></svg>
                          </a>
                        @endif
                        @if(hasPermission('delete users'))
                          <button type="button"
                            class="btn btn-outline-danger btn-icon js-user-delete"
                            data-delete-url="{{ route('users.destroy', $user->userId) }}"
                            title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>
                          </button>
                        @endif
                      </div>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!--User Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure want to delete?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
          <form id="deleteForm" action="" method="POST" style="display:inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger">Delete</button>
          </form>
      </div>
    </div>
  </div>
</div>

<!--User Add Wallet money -->
<div class="modal fade" id="walletmodal" tabindex="-1" role="dialog" aria-labelledby="walletmodalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="walletmodalLabel">Add Wallet Money</h5>
        </div>
        <div class="modal-body">
            <form id="addMoneyForm" action="{{ route('add-money', ':id') }}" method="POST">
                @csrf
                <input type="hidden" id="userId" name="userId"> <!-- Hidden field for user ID -->
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" class="form-control" id="amount" name="amount" required placeholder="Enter amount">
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add Money</button>
            </div>
                </form>
        </div>
    </div>
</div>

@if($collapseToTravclicksAndMasterDmc)
<!-- Master DMC team (DMC + other roles) modal -->
<div class="modal fade" id="masterDmcTeamModal" tabindex="-1" role="dialog" aria-labelledby="masterDmcTeamModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content mdmc-corp-modal">
      <div class="modal-header mdmc-corp-header">
        <div>
          <p class="mdmc-corp-eyebrow">Organization hierarchy</p>
          <h5 class="modal-title" id="masterDmcTeamModalLabel">Master DMC Team</h5>
          <p class="mdmc-corp-subtitle" id="masterDmcTeamSubtitle">Loading team users…</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body mdmc-corp-body">
        <div id="masterDmcTeamBody" class="mdmc-corp-list">
          <div class="mdmc-corp-empty">Loading…</div>
        </div>
      </div>
      <div class="modal-footer mdmc-corp-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@section('scripts')
<!-- In <head> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<!-- Before </body> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        const usersTable = $('.datatables-basic').DataTable({
            responsive: true,
            // Keep server order: Travclicks roles first, then Master DMC
            order: [],
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Enable copy, CSV, Excel, PDF, and Print buttons
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
        });

        function hideSettingsForNonEligibleRows() {
            const settingsColIndex = usersTable.column('.settings-controls-col').index();
            if (settingsColIndex === undefined) return;

            // In responsive child rows, hide this column's label/value for rows not eligible.
            $('.datatables-basic tbody tr.no-settings-row.parent').each(function() {
                const child = $(this).next('tr.child');
                if (!child.length) return;
                child.find('li[data-dt-column="' + settingsColIndex + '"]').hide();
            });
        }

        hideSettingsForNonEligibleRows();
        $('.datatables-basic').on('responsive-display.dt draw.dt', function() {
            hideSettingsForNonEligibleRows();
        });

        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            usersTable.button('.buttons-copy').trigger();
        });

        $('#exportCSV').on('click', function() {
            usersTable.button('.buttons-csv').trigger();
        });

        $('#exportExcel').on('click', function() {
            usersTable.button('.buttons-excel').trigger();
        });

        $('#exportPDF').on('click', function() {
            usersTable.button('.buttons-pdf').trigger();
        });

        $('#exportPrint').on('click', function() {
            usersTable.button('.buttons-print').trigger();
        });
    });
</script>
<!-- End DataTable JS -->

<script>
  function setDeleteForm(url, event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    var form = document.getElementById('deleteForm');
    if (form && url) {
      form.action = url;
    }

    openUsersPageModal('deleteModal');
  }

  function openUsersPageModal(id) {
    var el = document.getElementById(id);
    if (!el) {
      return;
    }

    if (el.parentElement !== document.body) {
      document.body.appendChild(el);
    }

    var alreadyOpen = document.querySelector('.modal.show');
    el.style.zIndex = (alreadyOpen && alreadyOpen !== el) ? '1110' : '1100';

    try {
      if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
        bootstrap.Modal.getOrCreateInstance(el).show();
        return;
      }
    } catch (e) {}

    try {
      var $el = window.jQuery ? window.jQuery(el) : null;
      if ($el && typeof $el.modal === 'function') {
        $el.modal('show');
        return;
      }
    } catch (e2) {}

    el.classList.add('show');
    el.style.display = 'block';
    el.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    if (!document.querySelector('.modal-backdrop')) {
      var backdrop = document.createElement('div');
      backdrop.className = 'modal-backdrop fade show';
      document.body.appendChild(backdrop);
    }
  }

  function closeUsersPageModal(id) {
    var el = document.getElementById(id);
    if (!el) {
      return;
    }

    try {
      if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
        var instance = bootstrap.Modal.getInstance(el);
        if (instance) {
          instance.hide();
          return;
        }
      }
    } catch (e) {}

    try {
      var $el = window.jQuery ? window.jQuery(el) : null;
      if ($el && typeof $el.modal === 'function') {
        $el.modal('hide');
        return;
      }
    } catch (e2) {}

    el.classList.remove('show');
    el.style.display = 'none';
    el.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.modal.show')) {
      document.body.classList.remove('modal-open');
      document.querySelectorAll('.modal-backdrop').forEach(function(node) {
        node.remove();
      });
    }
  }

  document.addEventListener('click', function(event) {
    var trigger = event.target.closest('.js-user-delete');
    if (!trigger) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    setDeleteForm(trigger.getAttribute('data-delete-url') || '', event);
  }, true);

  document.addEventListener('click', function(event) {
    var dismiss = event.target.closest('#deleteModal [data-bs-dismiss="modal"], #deleteModal [data-dismiss="modal"]');
    if (!dismiss) {
      return;
    }
    event.preventDefault();
    closeUsersPageModal('deleteModal');
  });
</script>

@if($collapseToTravclicksAndMasterDmc)
<script>
(function() {
    var loginUrlTemplate = @json(route('admin.loginAsUser', ['userId' => 0]));

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function buildLoginUrl(userId, providedUrl) {
        if (providedUrl) {
            return providedUrl;
        }
        return String(loginUrlTemplate).replace(/\/0(?:\?|$)/, '/' + encodeURIComponent(userId) + (String(loginUrlTemplate).indexOf('?') > -1 ? '?' : ''));
    }

    function initialsFromName(name) {
        var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return 'U';
        if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function renderMasterDmcTeam(team, meta) {
        var $body = $('#masterDmcTeamBody');
        meta = meta || {};

        if (!team || !team.length) {
            $body.html('<div class="mdmc-corp-empty">No users or roles created by this Master DMC.</div>');
            return;
        }

        var authUserId = meta.auth_user_id || 0;
        var showActionColumn = meta.show_action_column !== false;

        var cards = team.map(function(u, idx) {
            var uid = u.userId;
            var inactiveClass = u.is_active ? '' : ' is-inactive';

            var userHtml =
                '<div class="d-flex align-items-start gap-3">' +
                    '<div class="mdmc-avatar" title="#' + (idx + 1) + '">' + escapeHtml(initialsFromName(u.name)) + '</div>' +
                    '<div class="flex-grow-1 min-w-0">' +
                        '<p class="mdmc-user-name">' + escapeHtml(u.name) + '</p>' +
                        '<div class="mdmc-user-company">' + escapeHtml(u.company_name || 'N/A') + '</div>' +
                        '<div class="mdmc-pills">' +
                            '<span class="mdmc-pill mdmc-pill-role">' + escapeHtml(u.role) + '</span>' +
                            '<span class="mdmc-pill mdmc-pill-type">' + escapeHtml(u.user_type) + '</span>' +
                        '</div>' +
                        '<div class="mdmc-user-meta">' +
                            '<span><i class="fas fa-envelope"></i>' + escapeHtml(u.email || '—') + '</span>' +
                            (u.phone ? '<span><i class="fas fa-phone"></i>' + escapeHtml(u.phone) + '</span>' : '') +
                            '<span><i class="fas fa-map-marker-alt"></i>' + escapeHtml(u.user_country || 'N/A') +
                                (u.city ? ', ' + escapeHtml(u.city) : '') + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            var controls = [];
            if (u.show_settings_cell && u.show_settings_controls) {
                if (u.can_edit_settings) {
                    controls.push(controlSwitch('Zone', 'zone-toggle', 'modal_zone_on_' + uid, uid, u.zone_on, false));
                    controls.push(controlSwitch('Price', 'price-hide_toggle', 'modal_price_hide_' + uid, uid, u.price_hide, false));
                    controls.push(controlSwitch('Email', 'email-toggle', 'modal_email_on_' + uid, uid, u.email_on, false));
                    controls.push(
                        '<div class="mdmc-control-item">' +
                            '<span class="mdmc-label">Cancel</span>' +
                            '<div class="form-group auto-cancel-day-wrap mb-0" data-user-id="' + uid + '" style="display:' +
                                ((u.auto_cancel_date !== null && u.auto_cancel_date >= 1) ? 'block' : 'none') + ';">' +
                                '<select class="form-select auto-cancel-dropdown" data-user-id="' + uid + '" id="modal_auto_cancel_' + uid + '">' +
                                    buildAutoCancelOptions(u.auto_cancel_date) +
                                '</select>' +
                            '</div>' +
                            ((u.auto_cancel_date === null || u.auto_cancel_date < 1)
                                ? '<span class="text-muted" style="font-size:10px;">Off</span>'
                                : '') +
                        '</div>'
                    );
                    controls.push(
                        '<div class="mdmc-control-item">' +
                            '<span class="mdmc-label">AI</span>' +
                            '<select class="form-select ai-response-dropdown" data-user-id="' + uid + '" data-previous-value="' +
                                escapeHtml(u.ai_response || '') + '" id="modal_ai_response_' + uid + '" title="QTN = Quotation, ITN = Itinerary">' +
                                '<option value=""' + (!u.ai_response ? ' selected' : '') + '>--</option>' +
                                '<option value="QTN"' + (u.ai_response === 'QTN' ? ' selected' : '') + '>QTN</option>' +
                                '<option value="ITN"' + (u.ai_response === 'ITN' ? ' selected' : '') + '>ITN</option>' +
                            '</select>' +
                        '</div>'
                    );
                    controls.push(
                        '<div class="mdmc-control-item">' +
                            '<span class="mdmc-label">AI Resp</span>' +
                            '<select class="form-select ai-response-type-dropdown" data-user-id="' + uid + '" data-previous-value="' +
                                escapeHtml(u.ai_response_type || '') + '" id="modal_ai_response_type_' + uid + '" title="AI Response Type: Lite or Pro">' +
                                '<option value=""' + (!u.ai_response_type ? ' selected' : '') + '>--</option>' +
                                '<option value="lite"' + (u.ai_response_type === 'lite' ? ' selected' : '') + '>Lite</option>' +
                                '<option value="pro"' + (u.ai_response_type === 'pro' ? ' selected' : '') + '>Pro</option>' +
                            '</select>' +
                        '</div>'
                    );
                } else {
                    controls.push(controlSwitch('Zone', '', 'modal_zone_ro_' + uid, uid, u.zone_on, true));
                    controls.push(controlSwitch('Price', '', 'modal_price_ro_' + uid, uid, u.price_hide, true));
                    controls.push(controlSwitch('Email', '', 'modal_email_ro_' + uid, uid, u.email_on, true));
                    controls.push(
                        '<div class="mdmc-control-item">' +
                            '<span class="mdmc-label">Cancel</span>' +
                            '<span class="text-muted" style="font-size:11px;">' +
                                ((u.auto_cancel_date !== null && u.auto_cancel_date >= 1) ? ('D-' + u.auto_cancel_date) : 'Off') +
                            '</span>' +
                        '</div>'
                    );
                    controls.push(
                        '<div class="mdmc-control-item">' +
                            '<span class="mdmc-label">AI</span>' +
                            '<span class="text-muted" style="font-size:11px;">' + escapeHtml(u.ai_response || '—') + '</span>' +
                        '</div>'
                    );
                    controls.push(
                        '<div class="mdmc-control-item">' +
                            '<span class="mdmc-label">AI Resp</span>' +
                            '<span class="text-muted" style="font-size:11px;">' +
                                (u.ai_response_type ? (u.ai_response_type.charAt(0).toUpperCase() + u.ai_response_type.slice(1)) : '—') +
                            '</span>' +
                        '</div>'
                    );
                }
            }

            if (u.show_third_party) {
                controls.push(controlSwitch(
                    '3rd Party',
                    u.can_toggle_third_party ? 'third-party-toggle' : '',
                    'modal_thirdparty_enabled_' + uid,
                    uid,
                    u.thirdparty_enabled,
                    !u.can_toggle_third_party
                ));
            }

            if (u.show_booking_type) {
                var options = '';
                var bookingOpts = u.booking_options || {1: 'Lite Form', 2: 'Pro Form', 3: 'Both'};
                Object.keys(bookingOpts).forEach(function(val) {
                    options += '<option value="' + val + '"' +
                        (parseInt(u.selected_booking_type, 10) === parseInt(val, 10) ? ' selected' : '') + '>' +
                        escapeHtml(bookingOpts[val]) + '</option>';
                });
                controls.push(
                    '<div class="mdmc-control-item">' +
                        '<span class="mdmc-label">Booking</span>' +
                        '<select class="form-select booking-type-select" data-user-id="' + uid + '" data-previous-value="' +
                            escapeHtml(u.selected_booking_type) + '"' +
                            (u.booking_type_locked ? ' disabled' : '') + '>' + options + '</select>' +
                    '</div>'
                );
            }

            var settingsHtml = controls.length
                ? ('<div class="mdmc-section-label">Settings</div><div class="mdmc-controls">' + controls.join('') + '</div>')
                : '<div class="mdmc-section-label">Settings</div><span class="mdmc-empty-controls">No settings for this role</span>';

            var activeHtml =
                '<div class="mdmc-section-label text-center w-100">Status</div>' +
                '<div class="mdmc-status-block">' +
                    (u.can_edit
                        ? ('<div class="form-check form-switch mb-0">' +
                            '<input type="checkbox" class="form-check-input user-active-toggle" data-user-id="' + uid + '" id="modal_user_active_' + uid + '"' +
                            (u.is_active ? ' checked' : '') +
                            (parseInt(uid, 10) === parseInt(authUserId, 10) ? ' data-self="1"' : '') +
                            ' style="width:36px;height:18px;cursor:pointer;" title="' +
                            (u.is_active ? 'Active — click to deactivate' : 'Inactive — click to activate') + '">' +
                          '</div>')
                        : '') +
                    '<span class="mdmc-status-badge ' + (u.is_active ? 'is-active' : 'is-inactive') + '">' +
                        (u.is_active ? 'Active' : 'Inactive') +
                    '</span>' +
                '</div>';

            var loginUrl = buildLoginUrl(uid, u.login_url);
            var actionBits = [];
            if (u.is_active && loginUrl) {
                actionBits.push(
                    '<a href="' + escapeHtml(loginUrl) + '" class="btn btn-primary" title="Auto login as ' + escapeHtml(u.name) + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg>' +
                        ' Login' +
                    '</a>'
                );
            } else {
                actionBits.push(
                    '<button type="button" class="btn btn-secondary auto-login-disabled" disabled title="Auto login unavailable — user is inactive">Login</button>'
                );
            }

            var iconRow = '<div class="mdmc-actions-row">';
            if (showActionColumn && u.can_edit && u.edit_url) {
                iconRow +=
                    '<a href="' + escapeHtml(u.edit_url) + '" class="btn btn-outline-primary btn-icon" title="Edit">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor"><path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/></svg>' +
                    '</a>';
            }
            if (showActionColumn && u.can_delete && u.destroy_url) {
                iconRow +=
                    '<button type="button" class="btn btn-outline-danger btn-icon js-user-delete" data-delete-url="' + escapeHtml(u.destroy_url) + '" title="Delete">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="currentColor"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>' +
                    '</button>';
            }
            iconRow += '</div>';

            var actionHtml =
                '<div class="mdmc-section-label text-end w-100">Actions</div>' +
                '<div class="mdmc-actions">' +
                    actionBits.join('') +
                    iconRow +
                '</div>';

            return '<article class="mdmc-corp-card' + inactiveClass + '">' +
                '<div class="mdmc-corp-grid">' +
                    '<div>' + userHtml + '</div>' +
                    '<div>' + settingsHtml + '</div>' +
                    '<div>' + activeHtml + '</div>' +
                    '<div>' + actionHtml + '</div>' +
                '</div>' +
            '</article>';
        }).join('');

        $body.html(cards);
    }

    function controlSwitch(label, className, id, userId, checked, disabled) {
        return '<div class="mdmc-control-item">' +
            '<span class="mdmc-label">' + escapeHtml(label) + '</span>' +
            '<div class="form-check form-switch mb-0">' +
                '<input class="form-check-input' + (className ? (' ' + className) : '') + '"' +
                    (className ? (' data-user-id="' + userId + '"') : '') +
                    ' type="checkbox" id="' + id + '" value="1" role="switch"' +
                    (checked ? ' checked' : '') +
                    (disabled ? ' disabled' : '') + '>' +
            '</div>' +
        '</div>';
    }

    function buildAutoCancelOptions(selected) {
        var html = '';
        for (var d = 1; d <= 14; d++) {
            var isSelected = (selected == d) || ((selected === null || selected === undefined) && d === 1);
            html += '<option value="' + d + '"' + (isSelected ? ' selected' : '') + '>D-' + d + '</option>';
        }
        return html;
    }

    function openMasterDmcModal() {
        var $modal = $('#masterDmcTeamModal');
        if (!$modal.length) {
            return;
        }
        try {
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance($modal[0]).show();
                return;
            }
        } catch (e) {}
        try {
            if (typeof $modal.modal === 'function') {
                $modal.modal('show');
                return;
            }
        } catch (e2) {}

        // Last-resort fallback if Bootstrap modal APIs are unavailable
        $modal.addClass('show').css({ display: 'block' }).attr('aria-hidden', 'false');
        $('body').addClass('modal-open');
        if (!$('.modal-backdrop').length) {
            $('<div class="modal-backdrop fade show"></div>').appendTo(document.body);
        }
    }

    function loadMasterDmcTeam(masterId, masterName) {
        $('#masterDmcTeamModalLabel').text((masterName || 'Master DMC') + ' — Team Users');
        $('#masterDmcTeamSubtitle').text('Loading DMC and other roles…');
        $('#masterDmcTeamBody').html('<div class="mdmc-corp-empty">Loading team directory…</div>');
        openMasterDmcModal();

        var teamUrl = @json(route('users.master-dmc.team', ['masterDmcId' => 0]));
        teamUrl = teamUrl.replace(/\/0\/team$/, '/' + encodeURIComponent(masterId) + '/team');

        $.ajax({
            url: teamUrl,
            type: "GET",
            dataType: "json",
            success: function(response) {
                var team = (response && response.team) ? response.team : [];
                var meta = (response && response.meta) ? response.meta : {};
                $('#masterDmcTeamSubtitle').text(
                    team.length
                        ? (team.length + ' team member' + (team.length === 1 ? '' : 's') + ' under this Master DMC')
                        : 'No users or roles created by this Master DMC'
                );
                renderMasterDmcTeam(team, meta);
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Failed to load Master DMC team users.';
                $('#masterDmcTeamSubtitle').text(msg);
                $('#masterDmcTeamBody').html('<div class="mdmc-corp-empty text-danger">' + escapeHtml(msg) + '</div>');
            }
        });
    }

    function closeMasterDmcModal() {
        var $modal = $('#masterDmcTeamModal');
        try {
            if (window.bootstrap && bootstrap.Modal) {
                var instance = bootstrap.Modal.getInstance($modal[0]);
                if (instance) {
                    instance.hide();
                    return;
                }
            }
        } catch (e) {}
        try {
            if (typeof $modal.modal === 'function') {
                $modal.modal('hide');
                return;
            }
        } catch (e2) {}
        $modal.removeClass('show').css({ display: 'none' }).attr('aria-hidden', 'true');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }

    $(document).on('click', '#masterDmcTeamModal [data-dismiss="modal"], #masterDmcTeamModal [data-bs-dismiss="modal"]', function(e) {
        e.preventDefault();
        closeMasterDmcModal();
    });

    $(document).on('click', '.master-dmc-team-trigger', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var masterId = $(this).attr('data-master-id') || $(this).data('master-id');
        var masterName = $(this).attr('data-master-name') || $(this).data('master-name') || 'Master DMC';
        if (!masterId) {
            return;
        }
        loadMasterDmcTeam(masterId, masterName);
    });
})();
</script>
@endif


  <script>
      $(document).ready(function() {
          $('#walletmodal').on('show.bs.modal', function(event) {
              var button = $(event.relatedTarget); 
              var userId = button.data('id'); 
              var modal = $(this);
              modal.find('#userId').val(userId); 
              modal.find('#addMoneyForm').attr('action', '{{ url("add-money") }}/' + userId); 
          });
      });
  </script>

<script>
$(document).ready(function() {
    $('.travclicks-toggle').on('change', function() {
        const userId = $(this).data('user-id');
        const isChecked = $(this).is(':checked') ? 1 : 0;
        
        // Show loading indicator
        $(this).prop('disabled', true);
        
        $.ajax({
            url: "{{ route('users.update.travclicks') }}",
            type: "POST",
            data: {
                user_id: userId,
                travclicks_on: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Enable the toggle again
                $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show success message
                if (response.success) {
                    toastr.success(response.message || 'TravClicks status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating TravClicks status');
                    // Revert the toggle if there was an error
                    $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                // Enable the toggle again
                $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show error message
                toastr.error('Error updating TravClicks status');
                
                // Revert the toggle
                $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Use event delegation for price-hide toggle to work with pagination
    $(document).on('change', '.price-hide_toggle', function() {
        const userId = $(this).data('user-id');
        console.log("userId price-hide = ", userId);
        const isChecked = $(this).is(':checked') ? 1 : 0;
        
        // Show loading indicator
        $(this).prop('disabled', true);
        
        $.ajax({
            url: "{{ route('users.update.price-hide') }}",
            type: "POST",
            data: {
                user_id: userId,
                price_hide: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Enable the toggle again
                $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                console.log("userId price-hide = ", $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('disabled', false));

                
                // Show success message
                if (response.success) {
                    toastr.success(response.message || 'Price Hide status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating Price Hide status');
                    // Revert the toggle if there was an error
                    $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                // Enable the toggle again
                $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show error message
                toastr.error('Error updating Price Hide status');
                
                // Revert the toggle
                $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Use event delegation for zone toggle to work with pagination
    $(document).on('change', '.zone-toggle', function() {
        const userId = $(this).data('user-id');
        const isChecked = $(this).is(':checked') ? 1 : 0;
        console.log("this in zone-on");

        $(this).prop('disabled', true);  

        $.ajax({
            url: "{{ route('update.zoneon') }}",
            type: "POST",
            data: {
                user_id: userId,
                zone_on: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('.zone-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || 'Zone on status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating Zone on status');
                    $('.zone-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                $('.zone-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                toastr.error('Error updating Zone on status');
                $('.zone-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Use event delegation for email toggle to work with pagination
    $(document).on('change', '.email-toggle', function() {
        const userId = $(this).data('user-id');
        const isChecked = $(this).is(':checked') ? 1 : 0;
        
        // Show loading indicator
        $(this).prop('disabled', true);
        
        $.ajax({
            url: "{{ route('users.update.email') }}",
            type: "POST",
            data: {
                user_id: userId,
                email_on: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Enable the toggle again
                $('.email-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show success message
                if (response.success) {
                    toastr.success(response.message || 'Email status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating Email status');
                    // Revert the toggle if there was an error
                    $('.email-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                // Enable the toggle again
                $('.email-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show error message
                toastr.error('Error updating Email status');
                
                // Revert the toggle
                $('.email-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Booking Type select: Lite Form(1) / Pro From(2) / Both(3)
    $(document).on('change', '.booking-type-select', function() {
        const $select = $(this);
        const userId = $select.data('user-id');
        const bookingType = parseInt($select.val(), 10);
        const previousValue = parseInt($select.data('previous-value') || $select.find('option[selected]').val() || '1', 10);

        if (![1, 2, 3].includes(bookingType)) {
            toastr.error('Invalid booking type selected');
            $select.val(previousValue);
            return;
        }

        $select.prop('disabled', true);

        $.ajax({
            url: "{{ route('users.update.booking-type') }}",
            type: "POST",
            data: {
                user_id: userId,
                booking_type: bookingType,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $select.prop('disabled', false);
                if (response.success) {
                    $select.data('previous-value', bookingType);
                    toastr.success(response.message || 'Booking type updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating booking type');
                    $select.val(previousValue);
                }
            },
            error: function(xhr) {
                $select.prop('disabled', false);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error updating booking type';
                toastr.error(msg);
                $select.val(previousValue);
                console.error(xhr.responseText);
            }
        });
    });

    // Auto Cancel toggle: show/hide day dropdown and sync with backend
    $(document).on('change', '.auto-cancel-toggle', function() {
        const $toggle = $(this);
        const userId = $toggle.data('user-id');
        const isOn = $toggle.prop('checked');
        const $wrap = $('.auto-cancel-day-wrap[data-user-id="' + userId + '"]');
        const $dropdown = $('.auto-cancel-dropdown[data-user-id="' + userId + '"]');

        $wrap.css('display', isOn ? 'block' : 'none');

        $toggle.prop('disabled', true);
        $.ajax({
            url: "{{ route('update.autocancel') }}",
            type: "POST",
            data: {
                user_id: userId,
                auto_cancel_date: isOn ? ($dropdown.val() || '1') : '',
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $toggle.prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || (isOn ? 'Auto cancel enabled' : 'Auto cancel disabled'));
                } else {
                    toastr.error(response.message || 'Error updating auto cancel');
                }
            },
            error: function() {
                $toggle.prop('disabled', false);
                toastr.error('Error updating auto cancel');
                $wrap.css('display', isOn ? 'none' : 'block');
                $toggle.prop('checked', !isOn);
            }
        });
    });

    // Use event delegation for auto cancel dropdown to work with pagination
    $(document).on('change', '.auto-cancel-dropdown', function() {
        const userId = $(this).data('user-id');
        const selectedValue = $(this).val();
        console.log("Auto Cancel dropdown changed for user:", userId, "Value:", selectedValue);

        $(this).prop('disabled', true);

        $.ajax({
            url: "{{ route('update.autocancel') }}",
            type: "POST",
            data: {
                user_id: userId,
                auto_cancel_date: selectedValue,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || 'Auto cancel date updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating auto cancel date');
                    // Revert the dropdown to previous value
                    $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').val(response.previous_value || '');
                }
            },
            error: function(xhr) {
                $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').prop('disabled', false);
                toastr.error('Error updating auto cancel date');
                // Revert the dropdown to previous value
                $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').val('');
                console.error(xhr.responseText);
            }
        });
    });

    // Guide Pax input: save via AJAX on blur (when user leaves the field)
    $(document).on('blur', '.guide-pax-input', function() {
        const $input = $(this);
        const userId = $input.data('user-id');
        let value = $input.val().replace(/\D/g, '').slice(0, 2);
        if (value === '') value = '0';
        const num = parseInt(value, 10);
        const guidePax = isNaN(num) ? 0 : Math.max(0, Math.min(99, num));
        $input.val(guidePax);

        $input.prop('disabled', true);
        $.ajax({
            url: "{{ route('update.guidepax') }}",
            type: "POST",
            data: {
                user_id: userId,
                guide_pax: guidePax,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $input.prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || 'Guide pax updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating guide pax');
                    if (response.previous_value !== undefined) {
                        $input.val(response.previous_value);
                    }
                }
            },
            error: function(xhr) {
                $input.prop('disabled', false);
                toastr.error('Error updating guide pax');
                if (xhr.responseJSON && xhr.responseJSON.previous_value !== undefined) {
                    $input.val(xhr.responseJSON.previous_value);
                }
                console.error(xhr.responseText);
            }
        });
    });

    $(document).on('change', '.ai-response-dropdown:not(:disabled)', function() {
        const $dropdown = $(this);
        const userId = $dropdown.data('user-id');
        const selectedValue = $dropdown.val();
        const previousValue = $dropdown.data('previous-value') ?? '';

        $dropdown.prop('disabled', true);

        $.ajax({
            url: "{{ route('update.airesponse') }}",
            type: "POST",
            data: {
                user_id: userId,
                ai_response: selectedValue,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $dropdown.prop('disabled', false);
                if (response.success) {
                    $dropdown.data('previous-value', selectedValue);
                    toastr.success(response.message || 'AI Response updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating AI Response');
                    $dropdown.val(response.previous_value || previousValue || '');
                }
            },
            error: function(xhr) {
                $dropdown.prop('disabled', false);
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error updating AI Response';
                toastr.error(msg);
                $dropdown.val(previousValue || '');
                console.error(xhr.responseText);
            }
        });
    });

    $(document).on('focus', '.ai-response-dropdown:not(:disabled)', function() {
        $(this).data('previous-value', $(this).val());
    });

    $(document).on('change', '.ai-response-type-dropdown:not(:disabled)', function() {
        const $dropdown = $(this);
        const userId = $dropdown.data('user-id');
        const selectedValue = $dropdown.val();
        const previousValue = $dropdown.data('previous-value') ?? '';

        $dropdown.prop('disabled', true);

        $.ajax({
            url: "{{ route('update.airesponsetype') }}",
            type: "POST",
            data: {
                user_id: userId,
                ai_response_type: selectedValue,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $dropdown.prop('disabled', false);
                if (response.success) {
                    $dropdown.data('previous-value', selectedValue);
                    toastr.success(response.message || 'AI Response Type updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating AI Response Type');
                    $dropdown.val(response.previous_value || previousValue || '');
                }
            },
            error: function(xhr) {
                $dropdown.prop('disabled', false);
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error updating AI Response Type';
                toastr.error(msg);
                $dropdown.val(previousValue || '');
                console.error(xhr.responseText);
            }
        });
    });

    $(document).on('focus', '.ai-response-type-dropdown:not(:disabled)', function() {
        $(this).data('previous-value', $(this).val());
    });
});
</script>

<script>
$(document).ready(function() {
    $(document).on('change', '.third-party-toggle', function() {
        const $toggle = $(this);
        const userId = $toggle.data('user-id');
        const isChecked = $toggle.is(':checked');

        $toggle.prop('disabled', true);

        $.ajax({
            url: "{{ route('users.update.thirdparty-enabled') }}",
            type: "POST",
            data: {
                user_id: userId,
                thirdparty_enabled: isChecked ? 'yes' : 'no',
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $toggle.prop('disabled', false);
                if (response.success) {
                    $toggle.attr('title', isChecked ? 'Third party access on' : 'Third party access off');
                    toastr.success(response.message || 'Third party access updated successfully');
                } else {
                    $toggle.prop('checked', !isChecked);
                    toastr.error(response.message || 'Error updating third party access');
                }
            },
            error: function(xhr) {
                $toggle.prop('disabled', false);
                $toggle.prop('checked', !isChecked);
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error updating third party access';
                toastr.error(msg);
                console.error(xhr.responseText);
            }
        });
    });

    $(document).on('change', '.user-active-toggle', function() {
        const $toggle = $(this);
        const userId = $toggle.data('user-id');
        const isSelf = $toggle.data('self') === 1 || $toggle.data('self') === '1';
        const isChecked = $toggle.is(':checked') ? 1 : 0;

        if (isSelf && !isChecked) {
            toastr.warning('You cannot deactivate your own account.');
            $toggle.prop('checked', true);
            return;
        }

        $toggle.prop('disabled', true);

        $.ajax({
            url: "{{ route('users.update.active') }}",
            type: "POST",
            data: {
                user_id: userId,
                is_active: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'User status updated successfully');
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    $toggle.prop('disabled', false);
                    toastr.error(response.message || 'Error updating user status');
                    $toggle.prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                $toggle.prop('disabled', false);
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error updating user status';
                toastr.error(msg);
                $toggle.prop('checked', !isChecked);
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<!-- Add Typeahead.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
@endsection 
 