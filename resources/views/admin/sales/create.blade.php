@extends('layouts.admin')

@section('title', 'Create Sales Order')
@section('page-title', 'Create Sales Order')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Create Sales Order</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Orders
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.sales.store') }}" method="POST">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="so_no" class="form-label">Sales Order Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('so_no') is-invalid @enderror" id="so_no" name="so_no" value="{{ old('so_no') }}" required>
                    @error('so_no')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="item_select" class="form-label">Select Item <span class="text-danger">*</span></label>
                    <select class="form-select @error('item_id') is-invalid @enderror" id="item_select" required>
                        <option value="">-- Select Item --</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" 
                                data-name="{{ $item->item_name }}" 
                                data-category="{{ $item->category }}" 
                                data-hsn="{{ $item->hsn }}" 
                                data-rate="{{ $item->rate }}">
                                {{ $item->item_code }} - {{ $item->item_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('item_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('item_name') is-invalid @enderror" id="item_name" name="item_name" value="{{ old('item_name') }}" readonly required>
                    @error('item_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category') }}" readonly required>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="hsn" class="form-label">HSN Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('hsn') is-invalid @enderror" id="hsn" name="hsn" value="{{ old('hsn') }}" readonly required>
                    @error('hsn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="qty" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" min="1" class="form-control @error('qty') is-invalid @enderror" id="qty" name="qty" value="{{ old('qty', 1) }}" required>
                    @error('qty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="rate" class="form-label">Rate <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control @error('rate') is-invalid @enderror" id="rate" name="rate" value="{{ old('rate') }}" readonly required>
                        @error('rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="total" class="form-label">Total Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="text" class="form-control" id="total" readonly>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="reset" class="btn btn-light me-md-2">Reset</button>
                <button type="submit" class="btn btn-primary">Create Order</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemSelect = document.getElementById('item_select');
        const itemName = document.getElementById('item_name');
        const category = document.getElementById('category');
        const hsn = document.getElementById('hsn');
        const rate = document.getElementById('rate');
        const qty = document.getElementById('qty');
        const total = document.getElementById('total');
        
        // Function to calculate total
        function calculateTotal() {
            if (rate.value && qty.value) {
                const totalAmount = parseFloat(rate.value) * parseInt(qty.value);
                total.value = totalAmount.toFixed(2);
            } else {
                total.value = '';
            }
        }
        
        // Load item details when an item is selected
        itemSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                itemName.value = selectedOption.dataset.name;
                category.value = selectedOption.dataset.category;
                hsn.value = selectedOption.dataset.hsn;
                rate.value = selectedOption.dataset.rate;
                calculateTotal();
            } else {
                itemName.value = '';
                category.value = '';
                hsn.value = '';
                rate.value = '';
                total.value = '';
            }
        });
        
        // Recalculate total when quantity changes
        qty.addEventListener('input', calculateTotal);
    });
</script>
@endsection
@endsection