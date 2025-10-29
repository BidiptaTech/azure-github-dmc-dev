import React from 'react';
import { Tooltip } from '@mui/material';
import { styled } from '@mui/material/styles';
import PaymentDetailsTooltip from './PaymentDetailsTooltip';

const StyledTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'transparent',
    color: 'transparent',
    boxShadow: 'none',
    fontSize: '0.875rem',
    padding: 0,
    margin: 0,
    maxWidth: 'none',
  },
  '& .MuiTooltip-arrow': {
    display: 'none',
  },
}));

const CustomPaymentTooltip = ({ children, list, ...props }) => {
  return (
    <StyledTooltip
      title={<PaymentDetailsTooltip list={list} />}
      placement="bottom"
      arrow={false}
      enterDelay={300}
      leaveDelay={200}
      {...props}
    >
      {children}
    </StyledTooltip>
  );
};

export default CustomPaymentTooltip; 