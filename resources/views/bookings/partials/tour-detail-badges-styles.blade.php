@once
<style>
    .tour-detail-badges-row {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin: 0.2rem 0 0.35rem;
    }
    .tour-type-badge-3d,
    .tour-pro-lite-badge-3d {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.22rem 0.6rem;
        border-radius: 6px;
        font-weight: 800;
        line-height: 1.15;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.35);
        box-shadow:
            0 1px 0 rgba(255, 255, 255, 0.5) inset,
            0 -2px 0 rgba(0, 0, 0, 0.22) inset,
            0 2px 4px rgba(15, 23, 42, 0.2),
            0 4px 10px rgba(15, 23, 42, 0.14);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        vertical-align: middle;
        white-space: nowrap;
    }
    .tour-type-badge-3d {
        font-size: 0.68rem;
        letter-spacing: 0.45px;
        text-transform: uppercase;
        min-width: 42px;
    }
    .tour-type-badge-3d.group {
        background: linear-gradient(180deg, #a78bfa 0%, #7c3aed 42%, #5b21b6 100%);
        border-color: rgba(196, 181, 253, 0.65);
        min-width: 58px;
    }
    .tour-type-badge-3d.fit {
        background: linear-gradient(180deg, #34d399 0%, #059669 42%, #047857 100%);
        border-color: rgba(110, 231, 183, 0.65);
        min-width: 40px;
    }
    .tour-pro-lite-badge-3d {
        font-size: 0.62rem;
        letter-spacing: 0.35px;
        text-transform: uppercase;
        min-width: 38px;
        padding: 0.2rem 0.5rem;
    }
    .tour-pro-lite-badge-3d.pro {
        background: linear-gradient(180deg, #38bdf8 0%, #0284c7 45%, #075985 100%);
        border-color: rgba(125, 211, 252, 0.65);
    }
    .tour-pro-lite-badge-3d.lite {
        background: linear-gradient(180deg, #e2e8f0 0%, #94a3b8 45%, #64748b 100%);
        border-color: rgba(226, 232, 240, 0.55);
    }
</style>
@endonce
