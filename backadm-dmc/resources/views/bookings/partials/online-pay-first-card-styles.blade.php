<style>
    .online-pay-first-card {
        --opf-teal: #0f766e;
        --opf-teal-dark: #115e59;
        --opf-bg: #f0fdfa;
        --opf-border: #99f6e4;
        --opf-text: #134e4a;
        --opf-muted: #5b7c78;
        width: 100%;
        max-width: 200px;
        padding: 0.55rem 0.6rem 0.5rem;
        border-radius: 0.65rem;
        border: 1px solid var(--opf-border);
        background: linear-gradient(180deg, #ffffff 0%, var(--opf-bg) 100%);
        box-shadow: 0 1px 2px rgba(15, 118, 110, 0.06);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .online-pay-first-card.is-full-online {
        --opf-teal: #047857;
        --opf-teal-dark: #065f46;
        --opf-bg: #ecfdf5;
        --opf-border: #a7f3d0;
        --opf-text: #064e3b;
        --opf-muted: #3f6b5a;
    }
    .online-pay-first-card__top {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
    }
    .online-pay-first-card__title {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--opf-text);
        line-height: 1.25;
    }
    .online-pay-first-card__subtitle {
        margin: 0;
        font-size: 0.65rem;
        line-height: 1.3;
        color: var(--opf-muted);
    }
    .online-pay-first-card__offline {
        margin: 0;
        font-size: 0.64rem;
        line-height: 1.3;
        color: var(--opf-muted);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.2rem;
    }
    .online-pay-first-card__offline i {
        color: var(--opf-teal);
        font-size: 0.75rem;
    }
    .online-pay-first-card__offline strong {
        color: var(--opf-text);
        font-weight: 700;
    }
    .online-pay-first-card__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        width: 100%;
        border: 0;
        border-radius: 0.5rem;
        padding: 0.42rem 0.55rem;
        background: linear-gradient(135deg, var(--opf-teal) 0%, var(--opf-teal-dark) 100%);
        color: #fff !important;
        font-size: 0.74rem;
        font-weight: 650;
        line-height: 1.2;
        white-space: nowrap;
        box-shadow: 0 4px 10px rgba(15, 118, 110, 0.2);
        transition: filter 0.15s ease, box-shadow 0.15s ease;
        animation: onlinePayNowBlink 1.4s ease-in-out infinite;
    }
    .online-pay-first-card__btn:hover,
    .online-pay-first-card__btn:focus {
        color: #fff !important;
        filter: brightness(1.05);
        box-shadow: 0 6px 14px rgba(15, 118, 110, 0.26);
        animation: none;
    }
    @keyframes onlinePayNowBlink {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 4px 10px rgba(15, 118, 110, 0.2);
        }
        50% {
            opacity: 0.55;
            box-shadow: 0 2px 6px rgba(15, 118, 110, 0.12);
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .online-pay-first-card__btn {
            animation: none;
        }
    }
    .online-pay-first-card__btn i {
        font-size: 0.9rem;
    }
    .online-pay-first-card__steps {
        list-style: none;
        margin: 0.05rem 0 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .online-pay-first-card__steps li {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.62rem;
        color: var(--opf-muted);
        font-weight: 500;
    }
    .online-pay-first-card__steps li span {
        width: 1rem;
        height: 1rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.55rem;
        font-weight: 700;
        background: #d1fae5;
        color: var(--opf-teal-dark);
        flex-shrink: 0;
    }
    .online-pay-first-card__steps li.is-active {
        color: var(--opf-text);
        font-weight: 650;
    }
    .online-pay-first-card__steps li.is-active span {
        background: var(--opf-teal);
        color: #fff;
    }
    .online-pay-first-card__hint {
        margin: 0;
        font-size: 0.62rem;
        line-height: 1.3;
        color: var(--opf-muted);
    }

    .online-paid-hint-chip {
        display: inline-flex;
        align-items: flex-start;
        gap: 0.3rem;
        max-width: 200px;
        padding: 0.35rem 0.45rem;
        border-radius: 0.5rem;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        font-size: 0.64rem;
        line-height: 1.3;
    }
    .online-paid-hint-chip i {
        margin-top: 0.05rem;
        color: #059669;
        flex-shrink: 0;
    }
</style>
