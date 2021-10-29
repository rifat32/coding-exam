@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ route("search") }}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="" >please select</option>
                        @foreach ($variants as $variant )
                        <option value="" disabled><h2>{{$variant["title"]  }}</h2></option>
                        @foreach ($variant["filtered_product_variants"] as $variantList )
                        <option value="{{$variantList["variant"] }}">{{$variantList["variant"]  }}</option>
                        @endforeach
                            
                        @endforeach

                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($products as $product)
                    <tr>
                        <td>{{ $product["id"] }}</td>
                        <td>{{ $product["title"] }} <br> Created at : {{ \Carbon\Carbon::parse($product["created_at"])->diffForHumans(\Carbon\Carbon::now("Asia/Dhaka")) }}</td>
                        <td  >{{ $product["description"] }}</td>
                        
                        <td>
                            
                            <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">
                                @foreach ($product["product_variant_prices"] as $pvPrices)
                                <dt class="col-sm-3 pb-0">
                                   
                                        
                                    @if(empty(!$pvPrices["product_variant_one"]))
                                     
                                    {{ $pvPrices["product_variant_one"]["variant"] }}
                                   
                                   @endif
                                   @if(empty(!$pvPrices["product_variant_two"])) 
                                   {{ $pvPrices["product_variant_two"]["variant"] }} 
                                
                                @endif
                                @if(empty(!$pvPrices["product_variant_three"])) 
                                {{ $pvPrices["product_variant_three"]["variant"] }}
                                        
                                
                                @endif
                                <br>
                                   
                                 
                               
                                    
                                </dt>
                                
                                <dd class="col-sm-9">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 
                                        pb-0">Price : {{ number_format($pvPrices["price"],2) }}</dt>
                                        <dd class="col-sm-8 pb-0">InStock : {{ number_format($pvPrices["stock"],2) }}</dd>
                                    </dl>
                                </dd>
                                @endforeach
                            </dl>
                            <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product.edit', $product["id"]) }}" class="btn btn-success">Edit</a>
                            </div>
                        </td>
                    </tr>     
                    @endforeach
                    

                    </tbody>

                </table>
                {{-- {{ $products->links() }} --}}
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{ $productsObj->firstItem() }} to {{ $productsObj->lastItem() }} out of {{ $productsObj->total() }}</p>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    {{ $productsObj->appends(request()->query())->links() }} 
                </div>
            </div>
        </div>
    </div>

@endsection
