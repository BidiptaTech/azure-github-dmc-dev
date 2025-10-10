import React from 'react';
import { Box, Typography, Divider, Chip } from '@mui/material';
import { styled } from '@mui/material/styles';
import { useSelector } from 'react-redux';

const StyledTooltipContent = styled(Box)(({ theme }) => ({
  backgroundColor: 'rgba(255, 255, 255, 0.98)',
  backdropFilter: 'blur(10px)',
  border: '1px solid rgba(0, 0, 0, 0.12)',
  borderRadius: '8px',
  boxShadow: '0 6px 24px rgba(0, 0, 0, 0.15)',
  padding: '12px',
  minWidth: '220px',
  maxWidth: '260px',
  position: 'relative',
  '&::before': {
    content: '""',
    position: 'absolute',
    top: '-5px',
    left: '50%',
    transform: 'translateX(-50%)',
    width: '0',
    height: '0',
    borderLeft: '5px solid transparent',
    borderRight: '5px solid transparent',
    borderBottom: '5px solid rgba(255, 255, 255, 0.98)',
    filter: 'drop-shadow(0 -1px 2px rgba(0, 0, 0, 0.1))',
  },
}));

const PaymentRow = styled(Box)(({ theme, variant = 'default' }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  padding: '6px 8px',
  borderRadius: '6px',
  marginBottom: '4px',
  border: '1px solid',
  ...(variant === 'original' && {
    backgroundColor: 'rgba(25, 118, 210, 0.08)',
    borderColor: 'rgba(25, 118, 210, 0.2)',
  }),
  ...(variant === 'discount' && {
    backgroundColor: 'rgba(211, 47, 47, 0.08)',
    borderColor: 'rgba(211, 47, 47, 0.2)',
  }),
  ...(variant === 'final' && {
    backgroundColor: 'rgba(76, 175, 80, 0.08)',
    borderColor: 'rgba(76, 175, 80, 0.2)',
  }),
  ...(variant === 'status' && {
    backgroundColor: 'rgba(156, 39, 176, 0.08)',
    borderColor: 'rgba(156, 39, 176, 0.2)',
  }),
  ...(variant === 'due' && {
    backgroundColor: 'rgba(255, 152, 0, 0.08)',
    borderColor: 'rgba(255, 152, 0, 0.2)',
  }),
}));

const PaymentDetailsTooltip = ({ list }) => {
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  const getStatusColor = (status) => {
    switch (status) {
      case 'Not Paid':
        return '#d32f2f';
      case 'Partially Paid':
        return '#1976d2';
      case 'Completely Paid':
        return '#388e3c';
      default:
        return '#616161';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'Not Paid':
        return 'icon-x-circle';
      case 'Partially Paid':
        return 'icon-clock';
      case 'Completely Paid':
        return 'icon-check-circle';
      default:
        return 'icon-help-circle';
    }
  };

  return (
    <StyledTooltipContent>
      {/* Header */}
      <Box sx={{ mb: 1.5, textAlign: 'center' }}>
        <Typography variant="subtitle2" fontWeight={600} color="#1976d2" gutterBottom sx={{ fontSize: '0.875rem' }}>
          Payment Details
        </Typography>
        <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
          ID: {list.display_id}
        </Typography>
      </Box>

      <Divider sx={{ mb: 1.5 }} />

      {PriceHide === '0' ? (
        <>
          {/* Original Amount (from API, no tax) */}
          <PaymentRow variant="original">
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <i className="icon-tag" style={{ fontSize: '11px', color: '#1976d2' }}></i>
              <Typography variant="caption" fontWeight={600} color="#1976d2" sx={{ fontSize: '0.7rem' }}>
                Original
              </Typography>
            </Box>
            <Typography variant="caption" fontWeight={700} color="#1976d2" sx={{ fontSize: '0.7rem' }}>
              {(() => {
                const original = Math.ceil(
                  list.tour_total_price != null
                    ? Number(list.tour_total_price)
                    : Number(list.finalAmount || 0) + Number(list.discountAmount || 0)
                );
                return `SGD ${original}`;
              })()}
            </Typography>
          </PaymentRow>

          {/* Discount Amount (no tax) */}
          {list.discountAmount > 0 && (
            <PaymentRow variant="discount">
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <i className="icon-usd" style={{ fontSize: '11px', color: '#d32f2f' }}></i>
                <Typography variant="caption" fontWeight={600} color="#d32f2f" sx={{ fontSize: '0.7rem' }}>
                  Discount
                </Typography>
              </Box>
              <Typography variant="caption" fontWeight={700} color="#d32f2f" sx={{ fontSize: '0.7rem' }}>
                -SGD {Math.ceil(list.discountAmount)}
              </Typography>
            </PaymentRow>
          )}

          {/* Final Amount (API final + tax) */}
          <PaymentRow variant="final">
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <i className="icon-wallet" style={{ fontSize: '11px', color: '#4caf50' }}></i>
              <Typography variant="caption" fontWeight={600} color="#4caf50" sx={{ fontSize: '0.7rem' }}>
                Final
              </Typography>
            </Box>
            <Typography variant="caption" fontWeight={700} color="#4caf50" sx={{ fontSize: '0.7rem' }}>
              {(() => {
                const baseFinal = Number(list.finalAmountWithTax || 0);
                const withTax = Math.ceil(baseFinal);
                return `SGD ${withTax}`;
              })()}
            </Typography>
          </PaymentRow>

          {sgdTax > 0 && (
            <Box sx={{ textAlign: 'center', mt: 0.5 }}>
              <Typography variant="caption" sx={{ fontSize: '0.6rem', color: '#5E35B1', fontWeight: 600 }}>
                (Final amount incl. {sgdTax}% tax)
              </Typography>
            </Box>
          )}

        </>
      ) : (
        <Box sx={{ textAlign: 'center', my: 1 }}>
          <Typography variant="caption" sx={{ fontSize: '0.75rem', color: '#1976d2', fontWeight: 600 }}>
            Price Hidden
          </Typography>
        </Box>
      )}

      <Divider sx={{ my: 1 }} />

      {/* Payment Status */}
      <PaymentRow variant="status">
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
          <i 
            className={getStatusIcon(list.payment_status)} 
            style={{ fontSize: '11px', color: getStatusColor(list.payment_status) }}
          ></i>
          <Typography variant="caption" fontWeight={600} color={getStatusColor(list.payment_status)} sx={{ fontSize: '0.7rem' }}>
            Status
          </Typography>
        </Box>
        <Chip
          label={list.payment_status}
          size="small"
          sx={{
            backgroundColor: `${getStatusColor(list.payment_status)}15`,
            color: getStatusColor(list.payment_status),
            fontWeight: 600,
            fontSize: '0.65rem',
            height: '16px',
            '& .MuiChip-label': {
              px: 0.5,
            },
          }}
        />
      </PaymentRow>

      {/* Due Amount Warning (with tax) */}
      {list.dueAmount > 0 && (
        <PaymentRow variant="due">
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
            <i className="icon-alert-circle" style={{ fontSize: '11px', color: '#ff9800' }}></i>
            <Typography variant="caption" fontWeight={600} color="#ff9800" sx={{ fontSize: '0.7rem' }}>
              Due
            </Typography>
          </Box>
          <Typography variant="caption" fontWeight={700} color="#ff9800" sx={{ fontSize: '0.7rem' }}>
            {(() => {
              const baseDue = Number(list.dueAmount || 0);
              const withTax = Math.ceil(baseDue);
              return `SGD ${withTax}`;
            })()}
          </Typography>
        </PaymentRow>
      )}

      {/* Footer */}
      {/* <Divider sx={{ mt: 1.5, mb: 0.5 }} />
      <Box sx={{ textAlign: 'center' }}>
        <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
          Click for details
        </Typography>
      </Box> */}
    </StyledTooltipContent>
  );
};

export default PaymentDetailsTooltip; 