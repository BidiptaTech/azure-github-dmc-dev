@extends('layouts.master')

@section('title', 'Validations')
@section('css')
	
@endsection 
@section('content')
	<div class="d-flex justify-content-between align-items-center mb-3"
		style="background-color: #C0C0C0; color: #333; padding: 20px; border-radius: 5px;">
		<x-page-title title="Customer" pagetitle="Edit Customer" />
	</div>

	<div class="row">
		<div class="col-lg-12 mx-auto">
			<div class="card">
				<div class="card-header px-4 py-3">
					<h5 class="mb-0">Edit Customer Details</h5>
				</div>
				<div class="card-body p-4">
					<form id="jQueryValidationForm">
						<div class="row mb-3">
							<label for="input35" class="col-sm-3 col-form-label">Enter Your Name</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="input35" name="yourname" placeholder="Enter Your Name">
							</div>
						</div>
						<div class="row mb-3">
							<label for="input36" class="col-sm-3 col-form-label">Phone No</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="input36" name="phone" placeholder="Phone No">
							</div>
						</div>
						<div class="row mb-3">
							<label for="input37a" class="col-sm-3 col-form-label">Customername</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="input37a" name="Customername" placeholder="Email Address">
							</div>
						</div>
						<div class="row mb-3">
							<label for="input37" class="col-sm-3 col-form-label">Email Address</label>
							<div class="col-sm-9">
								<input type="email" class="form-control" id="input37" name="email" placeholder="Email Address">
							</div>
						</div>
						<div class="row mb-3">
							<label for="input38a" class="col-sm-3 col-form-label">Choose Password</label>
							<div class="col-sm-9">
								<input type="password" class="form-control" id="input38a" name="password" placeholder="Choose Password">
							</div>
						</div>
						<div class="row mb-3">
							<label for="input38" class="col-sm-3 col-form-label">Confirm Password</label>
							<div class="col-sm-9">
								<input type="password" class="form-control" id="input38" name="confirm_password" placeholder="Confirm Password">
							</div>
						</div>
						<div class="row mb-3">
							<label for="input39" class="col-sm-3 col-form-label">Select Country</label>
							<div class="col-sm-9">
								<select class="form-select" id="input39" name="country">
									<option selected disabled value>Choose...</option>
									<option value="1">One</option>
									<option value="2">Two</option>
									<option value="3">Three</option>
									</select>
							</div>
						</div>
						<div class="row mb-3">
							<label for="input40" class="col-sm-3 col-form-label">Address</label>
							<div class="col-sm-9">
								<textarea class="form-control" id="input40" name="address" rows="3" placeholder="Address"></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-form-label"></label>
							<div class="col-sm-9">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="input41" name="agree">
									<label class="form-check-label" for="input41">Check me out</label>
								</div>
							</div>
						</div>
						<div class="row">
							<label class="col-sm-3 col-form-label"></label>
							<div class="col-sm-9">
								<div class="d-md-flex d-grid align-items-center gap-3">
									<button type="submit" class="btn btn-primary px-4" name="submit2">Submit</button>
									<button type="reset" class="btn btn-secondary px-4">Reset</button>

								</div>
							</div>
						</div>
					</form>

				</div>
			</div>
		</div>
	</div>
@endsection  
