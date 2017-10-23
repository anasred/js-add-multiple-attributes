@extends('layouts.app')

@section('title', 'Create')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-header">{{__('trans.new_order')}}</h1>
            </div>
        </div>
        <form action="{{route('orders.store')}}" method="POST">
            {{ csrf_field()}}
            <div class="row">
                <div class="form-group{{ $errors->has('supplier_id') ? ' has-error' : '' }} col-md-8">
                    <label for="supplier_id">{{__('trans.supplier')}}</label>
                    <select class="form-control selectpicker" id="supplier_id" name="supplier_id" title="{{__('trans.supplier_id')}}" data-live-search="true" required>
                        @foreach ($suppliers as $supp)
                            <option value="{{$supp->id}}">{{$supp->name}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('supplier_id'))
                        <span class="help-block">
                            <strong>{{ $errors->first('supplier_id') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group{{ $errors->has('contact_id') ? ' has-error' : '' }} col-md-4">
                    <label for="contact_id">{{__('trans.contact_id')}}</label>
                    <select class="form-control selectpicker" id="contact_id" name="contact_id" title="{{__('trans.contact_id')}}" data-live-search="true" required>
                    </select>
                    @if ($errors->has('contact_id'))
                        <span class="help-block">
                            <strong>{{ $errors->first('contact_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }} col-md-6">
                    <label for="comment">{{__('trans.comment')}}</label>
                    <textarea type="text" class="form-control" id="comment" name="comment" placeholder="{{__('trans.comment')}}"></textarea>
                    @if ($errors->has('comment'))
                        <span class="help-block">
                            <strong>{{ $errors->first('comment') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group{{ $errors->has('delivery_comment') ? ' has-error' : '' }}  col-md-6">
                    <label for="delivery_comment">{{__('trans.delivery_comment')}}</label>
                    <textarea type="text" class="form-control" id="delivery_comment" name="delivery_comment" placeholder="{{__('trans.delivery_comment')}}"></textarea>
                    @if ($errors->has('delivery_comment'))
                      <span class="help-block">
                          <strong>{{ $errors->first('delivery_comment') }}</strong>
                      </span>
                    @endif
                </div>
                <input type="hidden" id="appOrderItems" name="orderItems[]">
            </div>
            <button type="submit" name="savingType" value="save_only" class="btn btn-primary">{{__('trans.save_as_draft')}}</button>
            <button type="submit" name="savingType" value="save_and_send" class="btn btn-success">{{__('trans.save_and_send')}}</button>
        </form>
        <div class="spacer"></div>
        <div class="row">
            <div class="col-md-6">
                <button type="button" onClick="showInputRow();" class="btn btn-default">
                    <i class="fa fa-plus"></i> {{__('trans.add_item')}}
                </button>
            </div>
            <div class="col-md-6">
                <h4>Order Total = <span id="orderTotal">0</span></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>{{__('trans.item_id')}}</th>
                                <th>{{__('trans.qty')}}</th>
                                <th>{{__('trans.exp_date')}}</th>
                                <th>{{__('trans.action')}}</th>
                            </tr>
                        </thead>
                        <tbody id="tBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row" id="inputRow" style="visibility:hidden;">
            <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }} col-md-4">
                <select class="form-control selectpicker" id="itemId" title="{{__('trans.item_id')}}" data-live-search="true" required>
                </select>
            </div>
            <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }} col-md-2">
                <input type="number" class="form-control" id="qty" placeholder="{{__('trans.qty')}}" min="0">
            </div>
            <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }} col-md-3">
                <input type="text" class="form-control" id="expDate" placeholder="Expiry Date" onfocus="(this.type='date')">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" id="item_price" readonly>
            </div>
            <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }} col-md-3">
                <button type="button" class="btn btn-success" onClick="persistRowToTable();">Add</button>
                <button type="button" class="btn btn-default" onClick="hideInputRow();">Cancel</button>
            </div>
        </div>
        <div clas="row">
            <div id="error" style="visibility:hidden;"></div>
        </div>
    </div>
@endsection
@push('js')
<script>
    var orderItems = [], appOrderItems = [], selectedItemName, supplierItems = [], orderTotal = 0, itemPrice = 0;

    // populate the contacts after selecting the supplier from the dropdown
    $('#supplier_id').on('change', function(e){
        $('#contact_id').empty();
        $('#itemId').empty();
        supplierItems.length = 0;
        
        $.ajax({
            method: 'GET',
            url: '/ajax/suppliers/' + e.target.value + '/get-contacts',
            success: function(data){
                data.forEach(function(el) {
                    $('#contact_id').append('<option value="' + el.id + '">' + el.name + '</option>');
                });
                $('#contact_id').selectpicker('refresh');
            },
        });

        $.ajax({
            method: 'GET',
            url: '/ajax/suppliers/' + e.target.value + '/items',
            success: function(data){
                supplierItems = data;
                data.forEach(function(el) {
                    $('#itemId').append(
                        '<option data-price="' + el.unit_price + '" value="' + el.id + '">' + 'Cat No. ' + el.cat_no + ' - Name: ' + el.name + ' - Price: ' + el.unit_price + '</option>'
                    );
                });
                $('#itemId').selectpicker('refresh');
            },
        });
    });

    // save the item name from bootstrap select dropdown menu
    $('#itemId').on('change', function(e){
        selectedItemName = $(this).find('option:selected').text();

        itemPrice = 0;
        itemPrice = e.target.selectedOptions[0].dataset.price;
    });

    function showInputRow() {
        $('#inputRow').css('visibility', 'visible');
    };

    function hideInputRow() {
        $('#inputRow').css('visibility', 'hidden');
        $('#expDate').attr('type', 'text');
        $('#error').css('visibility', 'hidden');
        clearInputValues();
    }

    function persistRowToTable() {
        $('#error').empty();

        orderTotal += itemPrice * $('#qty').val();
        $('#orderTotal').empty().append(orderTotal);

        if ($('#itemId').val() == '' || $('#qty').val() == '' || $('#expDate').val() == '') {
            $('#error').css('visibility', 'visible');
            return $('#error').append(
                `
                    <span class="help-block">
                        <strong class="text-danger">All Fields be filled</strong>
                    </span>
                `
            );
        }

        orderItems.push({
            id : $('#itemId').val(),
            itemName : selectedItemName,
            qty : $('#qty').val(),
            exp_date : $('#expDate').val(),
            unit_price: itemPrice,
        });

        $('#tBody').empty();

        orderItems.forEach(function (el) {
            $('#tBody').append(
                `
                    <tr id="${el.id}">
                        <td>${el.itemName}</td>
                        <td>${el.qty}</td>
                        <td>${el.exp_date}</td>
                        <td>
                            <button type="button" onClick="removeItemFromArray(${el.id});" class="btn btn-danger">
                                <i class="fa fa-times fa-lg"></i> Delete
                            </button>
                        </td>
                    </tr>
                `
            );
        });

        syncOrderItemsToAppOrderItems();
        hideInputRow();
    }

    function syncOrderItemsToAppOrderItems() {
        appOrderItems.length = 0;

        orderItems.forEach(function(el){
            appOrderItems.push({
                id: el.id,
                qty: el.qty,
                exp_date: el.exp_date,
                unit_price: el.unit_price
            });
        });

        $('#appOrderItems').val(JSON.stringify(appOrderItems));
    }

    // remove order item from orderItems and sync it with appOrderItems
    function removeItemFromArray(itemId) {
        $('#' + itemId).remove();

        var index = orderItems.findIndex(function (el) {
            return el.id == itemId;
        });

        if (index > -1) {
            orderItems.splice(index, 1);
        };

        syncOrderItemsToAppOrderItems();
    }

    // use 'n' key on the keyboard to add more item to the order
    function appkeyUp(e) {
        if (e.keyCode == 78) {
            showInputRow();
        }
    }

    // register the handler
    document.addEventListener('keyup', appkeyUp, false);

    // clear all input fields
    function clearInputValues() {
        $('#itemId').val('');
        $('#qty').val('');
        $('#expDate').val('');
    }
</script>
@endpush
