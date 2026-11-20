<style>
    .dmc-badge-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .dmc-color-badge {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 6px 8px 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
        white-space: normal;
        word-break: break-word;
    }
    .dmc-color-badge .dmc-badge-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        margin-left: 6px;
        border: 0;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.12);
        color: inherit;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
    }
    .dmc-color-badge .dmc-badge-remove:hover {
        background: rgba(15, 23, 42, 0.22);
    }
    .dmc-assigned-preview {
        min-height: 46px;
        padding: 8px 10px;
        border: 1px dashed #d9dee3;
        border-radius: 8px;
        background: #f8f9fa;
    }
    .dmc-combo {
        position: relative;
    }
    .dmc-combo-dropdown {
        position: absolute;
        z-index: 30;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        max-height: 240px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
    }
    .dmc-combo-item {
        display: block;
        width: 100%;
        text-align: left;
        padding: 8px 12px;
        border: 0;
        background: transparent;
        color: inherit;
        cursor: pointer;
    }
    .dmc-combo-item:hover,
    .dmc-combo-item.is-active {
        background: #eef4ff;
    }
    .dmc-combo-empty {
        padding: 10px 12px;
        color: #6c757d;
        font-size: 13px;
    }
</style>
