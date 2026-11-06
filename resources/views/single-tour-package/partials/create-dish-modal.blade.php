<!-- Dish Selection Modal -->
<div class="modal fade" id="dishSelectionModal" tabindex="-1" aria-labelledby="dishSelectionModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dishSelectionModalLabel">Select Dish</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dishModalContent">
                    <!-- Dynamic content will be loaded here -->
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shopping-cart text-success me-2"></i>
                            <span class="fw-bold">Total Price:</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span id="modalTotalPrice" class="h4 text-success">$0.00</span>
                        <br>
                        <small id="modalGuestInfo" class="text-muted"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCEL</button>
                <button type="button" class="btn btn-success" id="confirmDishSelection" disabled>
                    <i class="fas fa-check me-2"></i>CONFIRM SELECTION
                </button>
            </div>
        </div>
    </div>


</div>
