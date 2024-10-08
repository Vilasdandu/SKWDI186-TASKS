<?php

namespace App\Controllers;

use App\Exceptions\ProductException;
use App\Facades\Request;
use App\Facades\Response;
use App\Models\Product;
use Rakit\Validation\Validator;

class ProductController extends Controller
{
    public function index()
    {
        return Response::json(Product::all());
    }

    public function show(int $id)
    {
        $product = Product::find($id);

        if ($product) {
            return $product;
        }
        return Response::notFound();
    }

    public function store()
    {
        $data = Request::post();
        $validation = (new Validator())->make($data, [
            "title" => "required|max:255",
            "description" => "required",
            "price" => "required|numeric",
            "category_id" => "required|numeric",
            "seller_id" => "required|numeric",
        ]);

        $validation->validate();

        if ($validation->fails()) {
            return ProductException::invalid_posted_data();
        }

        $product = new Product();

        $product->title = $data["title"];
        $product->description = $data["description"];
        $product->price = $data["price"];
        $product->category_id = $data["category_id"];
        $product->seller_id = $data["seller_id"];

        $product->save();

        return Response::json([
            "detail" => "success"
        ]);
    }

    public function update(int $id)
    {
        $data = Request::update();

        $validation = (new Validator())->make($data, [
            "title" => "max:255",
            "description" => "",
            "price" => "numeric",
            "category_id" => "numeric",
            "seller_id" => "numeric"
        ]);

        $validation->validate();

        $updatedProduct = Product::update($id, $data);
        return $updatedProduct;
    }

    public function destroy(int $id)
    {
        $validation = (new Validator())->make(["id" => $id], [
            "id" => "required|numeric"
        ]);

        if ($validation->fails()) {
            return ProductException::error("id is required");
        }

        return Product::destroy($id);
    }
}
