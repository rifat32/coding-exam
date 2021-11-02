<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $productQuery = Product::with(
            "productVariantPrices.productVariantOne",
            "productVariantPrices.productVariantTwo",
            "productVariantPrices.productVariantThree"
        );
        $productsObj = $productQuery->orderByDesc("id")->paginate(7);
        $products = collect($productsObj->items())->toArray();
        // return $productsObj;
        $productVarient = Variant::with("productVariants")->get();
        $variants = $productVarient->map(function ($array) {

            $array["filtered_product_variants"] = collect($array->toArray()["product_variants"])->unique('variant');

            return $array;
        });
        // return $variants;
        return view('products.index', compact("products", "productsObj", "variants"));
    }
    public function search(Request $request)
    {

        $title = $request->title;
        $date = $request->date;
        $req_variants = $request->variant;
        $price_from = $request->price_from;
        $price_to = $request->price_to;

        $productQuery = Product::with(
            "productVariantPrices.productVariantOne",
            "productVariantPrices.productVariantTwo",
            "productVariantPrices.productVariantThree"
        );
        if ($title) {
            $productQuery =    $productQuery->where('title', 'like', '%' . $title . '%');
        }
        if ($date) {
            $productQuery = $productQuery->whereDate('created_at', '=', $date);
        }
        $products = $productQuery->get();
        if ($req_variants) {

            $products = $products->filter(function ($product) use (&$req_variants) {



                $variantNotEmpty =   collect($product->toArray()["product_variant_prices"])->filter(function ($variant) use (&$req_variants) {



                    if ($variant["product_variant_one"]["variant"] == $req_variants) {
                        return true;
                    } else if ($variant["product_variant_two"]["variant"] ==  $req_variants) {
                        return true;
                    } else if ($variant["product_variant_three"]) {
                        if ($variant["product_variant_three"]["variant"] == $req_variants) {
                            return true;
                        }
                    } else {
                        return false;
                    }
                })->isNotEmpty();

                if ($variantNotEmpty) {
                    return true;
                }
            });
        }
        if ($price_from) {
            $products = $products->filter(function ($product) use (&$price_from) {

                $variantNotEmpty =   collect($product->toArray()["product_variant_prices"])->filter(function ($variant) use (&$price_from) {



                    if ($variant["price"] >= $price_from) {
                        return true;
                    } else {
                        return false;
                    }
                })->isNotEmpty();

                if ($variantNotEmpty) {
                    return true;
                }
            });
        }
        if ($price_to) {
            $products = $products->filter(function ($product) use (&$price_to) {

                $variantNotEmpty =   collect($product->toArray()["product_variant_prices"])->filter(function ($variant) use (&$price_to) {



                    if ($variant["price"] <= $price_to) {
                        return true;
                    } else {
                        return false;
                    }
                })->isNotEmpty();

                if ($variantNotEmpty) {
                    return true;
                }
            });
        }







        $productVarient = Variant::with("productVariants")->get();
        $variants = $productVarient->map(function ($array) {

            $array["filtered_product_variants"] = collect($array->toArray()["product_variants"])->unique('variant');

            return $array;
        });
        $products = $products->toArray();
        return view('products.search', compact("products", "variants"));

        return $products;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {


        DB::transaction(function () use (&$request) {
            $product = new Product();

            $product->title = $request->title;
            $product->sku = $request->sku;
            $product->description = $request->description;
            $product->save();


            $variant_one = $variant_two =  $variant_three = null;

            foreach ($request->product_image as $image) {
                $product_image = new ProductImage();
                $product_image->file_path = $image;
                $product_image->product_id = $product->id;
                $product_image->save();
            }
            $ab = "a";
            foreach ($request->product_variant as $key => $product_variant) {
                $variant_one = null;
                $variant_two = null;
                $variant_three = null;

                foreach ($product_variant["tags"] as  $key2 => $tag) {
                    $productVariant = new ProductVariant();
                    $productVariant->variant = $tag;
                    $productVariant->variant_id = $product_variant["option"];

                    $productVariant->product_id = $product->id;
                    $productVariant->save();
                    if ($key2 == 0) {
                        $variant_one = $productVariant->id;
                    }
                    if ($key2 == 1) {
                        $variant_two = $productVariant->id;
                    }
                    if ($key2 == 2) {
                        $variant_three = $productVariant->id;
                    }
                }


                foreach ($request->product_variant_prices as $key3 => $product_variant_price) {
                    $productVariantPrice = new ProductVariantPrice();
                    $productVariantPrice->price = $product_variant_price["price"];
                    $productVariantPrice->stock = $product_variant_price["stock"];
                    $productVariantPrice->product_variant_one = $variant_one;
                    $productVariantPrice->product_variant_two = $variant_two;
                    $productVariantPrice->product_variant_three = $variant_three;
                    $productVariantPrice->product_id =  $product->id;
                    $productVariantPrice->save();
                }
            }
        });





        return response()->json(["message" => "data saved"], 201);
    }
    public function storeImage(Request $request)
    {
        $imageName = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move(public_path('images'), $imageName);
        return response()->json(["image" => $imageName]);
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {

        $variants = Variant::all();
        // $productImages = ProductImage::where(["product_id" => $product->id])->get();
        $productVariantsPrices = ProductVariantPrice::where(["product_id" => $product->id])->with("productVariantOne.variantMain", "productVariantTwo.variantMain", "productVariantThree.variantMain")->get();

        // return $productImages;
        return view('products.edit', compact('variants', 'product', "productVariantsPrices"));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        DB::transaction(function () use (&$request, &$product) {

            $product->title = $request->title;
            $product->sku = $request->sku;
            $product->description = $request->description;
            $product->save();

            ProductVariant::where(["product_id" => $product->id])->delete();
            ProductVariantPrice::where(["product_id" => $product->id])->delete();
            // $abcd = ProductVariant::where(["product_id" => $product->id])->get();



            $variant_one = $variant_two =  $variant_three = null;


            foreach ($request->product_variant as $key => $product_variant) {


                foreach ($product_variant["tags"] as  $key2 => $tag) {


                    $productVariant = new  ProductVariant();



                    $productVariant->variant = $tag;
                    $productVariant->variant_id = $product_variant["option"];

                    $productVariant->product_id = $product->id;
                    $productVariant->save();
                    if ($key2 == 0) {
                        $variant_one = $productVariant->id;
                    }
                    if ($key2 == 1) {
                        $variant_two = $productVariant->id;
                    }
                    if ($key2 == 2) {
                        $variant_three = $productVariant->id;
                    }
                }


                foreach ($request->product_variant_prices as $key3 => $product_variant_price) {



                    $productVariantPrice = new ProductVariantPrice();


                    $productVariantPrice->price = $product_variant_price["price"];
                    $productVariantPrice->stock = $product_variant_price["stock"];
                    $productVariantPrice->product_variant_one = $variant_one;
                    $productVariantPrice->product_variant_two = $variant_two;
                    $productVariantPrice->product_variant_three = $variant_three;
                    $productVariantPrice->product_id =  $product->id;
                    $productVariantPrice->save();
                }
            }
        });


        return response()->json(["message" => "data updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
