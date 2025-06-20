import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
  Grid,
  Card,
  IconButton,
  Radio,
  FormControlLabel,
  RadioGroup,
  CircularProgress
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';

/**
 * A reusable modal component for selecting items (hotels, attractions, restaurants, etc.)
 * 
 * @param {boolean} open - Whether the modal is open
 * @param {function} onClose - Function to call when closing the modal
 * @param {string} title - Title of the modal
 * @param {Array} items - Array of items to display for selection
 * @param {Array} selectedItems - Array of currently selected items
 * @param {function} onSelectionChange - Function to call when selection changes
 * @param {function} renderItem - Function to render each item
 * @param {boolean} loading - Whether the items are loading
 */
const SelectionModal = ({ 
  open, 
  onClose, 
  title, 
  items = [], 
  selectedItems = [], 
  onSelectionChange,
  renderItem,
  loading = false
}) => {
  // State to track the selected item ID
  const [selectedItemId, setSelectedItemId] = useState('');
  
  // Update selected item ID when modal opens or selectedItems changes
  useEffect(() => {
    if (open && selectedItems.length > 0) {
      const selectedItem = selectedItems[0];
      setSelectedItemId(selectedItem.id || selectedItem._id || '');
    }
  }, [open, selectedItems]);
  
  const handleSelectionChange = (event) => {
    setSelectedItemId(event.target.value);
  };
  
  const isSelected = (item) => {
    return (item.id || item._id) === selectedItemId;
  };
  
  const handleSave = () => {
    // Find the selected item in the items array
    const selectedItem = items.find(item => (item.id || item._id) === selectedItemId);
    
    // If an item is selected, pass it as an array with one item
    // Otherwise, pass an empty array
    onSelectionChange(selectedItem ? [selectedItem] : []);
    onClose();
  };
  
  // Function to handle cancel - just close the modal without saving changes
  const handleCancel = () => {
    // Reset selection to match the current selectedItems prop
    if (selectedItems.length > 0) {
      const selectedItem = selectedItems[0];
      setSelectedItemId(selectedItem.id || selectedItem._id || '');
    } else {
      setSelectedItemId('');
    }
    onClose();
  };
  
  return (
    <Dialog 
      open={open} 
      onClose={handleCancel} 
      fullWidth 
      maxWidth="md"
      PaperProps={{
        sx: { borderRadius: '8px' }
      }}
    >
      <DialogTitle sx={{ 
        bgcolor: 'primary.main', 
        color: 'primary.contrastText',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        py: 1
      }}>
        <Typography variant="h6">{title}</Typography>
        <IconButton 
          size="small" 
          onClick={handleCancel}
          sx={{ color: 'inherit' }}
        >
          <CloseIcon />
        </IconButton>
      </DialogTitle>
      
      <DialogContent sx={{ py: 2 }}>
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', py: 4 }}>
            <CircularProgress />
            <Typography variant="body1" sx={{ ml: 2 }}>Loading items...</Typography>
          </Box>
        ) : items.length === 0 ? (
          <Box sx={{ textAlign: 'center', py: 4 }}>
            <Typography variant="body1">No items available for selection.</Typography>
          </Box>
        ) : (
          <RadioGroup
            value={selectedItemId}
            onChange={handleSelectionChange}
          >
            <Grid container spacing={2}>
              {items.map((item) => {
                const itemId = item.id || item._id || '';
                return (
                  <Grid item xs={12} sm={6} md={4} key={itemId}>
                    <Card 
                      elevation={0}
                      sx={{ 
                        border: '1px solid',
                        borderColor: isSelected(item) ? 'primary.main' : 'divider',
                        borderRadius: '4px',
                        overflow: 'hidden',
                        position: 'relative',
                        cursor: 'pointer',
                        transition: 'all 0.2s',
                        '&:hover': {
                          borderColor: 'primary.main',
                          boxShadow: '0 0 0 1px rgba(25, 118, 210, 0.3)'
                        }
                      }}
                      onClick={() => setSelectedItemId(itemId)}
                    >
                      <Box sx={{ position: 'absolute', top: 8, right: 8, zIndex: 2 }}>
                        <Radio 
                          checked={isSelected(item)} 
                          value={itemId}
                          color="primary"
                          sx={{ 
                            bgcolor: 'rgba(255, 255, 255, 0.8)',
                            borderRadius: '50%',
                            p: 0.25,
                            '&.Mui-checked': {
                              color: 'primary.main',
                            }
                          }}
                        />
                      </Box>
                      {renderItem(item)}
                    </Card>
                  </Grid>
                );
              })}
            </Grid>
          </RadioGroup>
        )}
      </DialogContent>
      
      <DialogActions sx={{ px: 3, py: 2 }}>
        <Button onClick={handleCancel} variant="outlined">
          Cancel
        </Button>
        <Button 
          onClick={handleSave} 
          variant="contained"
          disabled={loading || !selectedItemId}
        >
          Save Selection
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default SelectionModal; 