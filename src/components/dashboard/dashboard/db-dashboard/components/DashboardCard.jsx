import React from 'react';
import { 
  PendingActions, 
  CurrencyExchange, 
  Hotel, 
  Attractions 
} from '@mui/icons-material';

// Icons can be imported from Material UI or use custom SVGs
const iconComponents = {
  Pending: <PendingActions style={{ fontSize: 40, color: '#FF9800' }} />,
  Earnings: <CurrencyExchange style={{ fontSize: 40, color: '#4CAF50' }} />,
  Bookings: <Hotel style={{ fontSize: 40, color: '#2196F3' }} />,
  Services: <Attractions style={{ fontSize: 40, color: '#9C27B0' }} />,
};

const cardColors = {
  Pending: { 
    background: 'linear-gradient(135deg, #FFF8E6 0%, #FFECB3 100%)',
    iconBg: 'rgba(255, 152, 0, 0.1)',
    textColor: '#FF9800',
    shadow: '0 6px 15px rgba(255, 152, 0, 0.15)'
  },
  Earnings: { 
    background: 'linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%)',
    iconBg: 'rgba(76, 175, 80, 0.1)',
    textColor: '#4CAF50',
    shadow: '0 6px 15px rgba(76, 175, 80, 0.15)'
  },
  Bookings: { 
    background: 'linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%)',
    iconBg: 'rgba(33, 150, 243, 0.1)',
    textColor: '#2196F3',
    shadow: '0 6px 15px rgba(33, 150, 243, 0.15)'
  },
  Services: { 
    background: 'linear-gradient(135deg, #F3E5F5 0%, #E1BEE7 100%)',
    iconBg: 'rgba(156, 39, 176, 0.1)',
    textColor: '#9C27B0',
    shadow: '0 6px 15px rgba(156, 39, 176, 0.15)'
  },
};

const data = [
  {
    title: "Pending",
    amount: "$12,800",
    description: "Total pending",
  },
  {
    title: "Earnings",
    amount: "$14,200",
    description: "Total earnings",
  },
  {
    title: "Bookings",
    amount: "$8,100",
    description: "Total hotel bookings",
  },
  {
    title: "Services",
    amount: "22,786",
    description: "Total bookable services",
  },
];

const DashboardCard = () => {
  return (
    <div className="row" style={{ 
      gap: '10px', 
      //margin: '0 -12px 24px',
      display: 'flex',
      flexWrap: 'nowrap',
      //overflowX: 'auto',
      padding: '0 12px'
    }}>
      {data.map((item, index) => (
        <div key={index} className="col-xl-3 col-md-6" style={{ 
          padding: '0 12px',
          flex: '0 0 25%',
          minWidth: '280px'
        }}>
          <div 
            className="dashboard-card" 
            style={{
              borderRadius: '16px',
              padding: '25px',
              background: cardColors[item.title].background,
              boxShadow: cardColors[item.title].shadow,
              transition: 'all 0.3s ease',
              position: 'relative',
              overflow: 'hidden',
              height: '100%',
              cursor: 'pointer',
            }}
          >
            {/* Subtle pattern overlay */}
            <div style={{
              position: 'absolute',
              top: 0,
              left: 0,
              width: '100%',
              height: '100%',
              backgroundImage: 'radial-gradient(circle, rgba(255,255,255,0.8) 1px, transparent 1px)',
              backgroundSize: '20px 20px',
              opacity: 0.3,
              zIndex: 0
            }}></div>
            
            <div className="row" style={{ position: 'relative', zIndex: 1 }}>
              <div className="col-8">
                <div style={{ 
                  fontSize: '16px', 
                  fontWeight: '600',
                  color: cardColors[item.title].textColor 
                }}>
                  {item.title}
                </div>
                <div style={{ 
                  fontSize: '28px', 
                  fontWeight: '700', 
                  margin: '10px 0 5px',
                  color: '#333'
                }}>
                  {item.amount}
                </div>
                <div style={{ 
                  fontSize: '14px',
                  color: '#666',
                  fontWeight: '400'
                }}>
                  {item.description}
                </div>
              </div>
              <div className="col-4" style={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center' }}>
                <div style={{ 
                  width: '70px',
                  height: '70px',
                  borderRadius: '50%',
                  backgroundColor: cardColors[item.title].iconBg,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}>
                  {iconComponents[item.title]}
                </div>
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};

export default DashboardCard;
