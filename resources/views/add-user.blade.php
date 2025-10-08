function updateFields() {
    var userRole = userTypeSelect.value;
    inputRoleContainer.style.display = 'none';
    inputDmcContainer.style.display = 'none';
    country_name.style.display = 'none';
    country_names.style.display = 'none';
    master_logo.style.display = 'none';
    inputSalespersonContainerAdmin.style.display = 'none';
    // markuptypes.style.display = 'none';
    mastercountryContainer.style.display = 'none';

    resetHiddenFieldValues();

    
    if (userRole >= 5 && userRole <= 9) {
        country_names.style.display = 'block';
    } else if (userRole == 10){
        country_names.style.display = 'block';
        master_logo.style.display = 'block';
    } else if (userRole == 11) {
        inputRoleContainer.style.display = 'block';
        // markuptypes.style.display = 'flex';
    } else if (userRole == 4) {
        inputSalespersonContainerAdmin.style.display = 'block';
    } else if (userRole == 3) {
        country_name.style.display = 'block';
    }
} 