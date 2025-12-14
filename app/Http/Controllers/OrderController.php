<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Product;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::query()->orderByDesc('id')->paginate(15);
    }

    public function addToOrder(Product $product)
    {
        $order = Order::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'status' => 'draft'
            ]
        );

        $item = $order->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->increment('quantity');
            $item->update([
                'total' => $item->quantity * $item->price
            ]);
        } else {
            $order->items()->create([
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => 1,
                'total' => $product->price,
            ]);
        }

        $this->recalculateTotal($order);

        return back();
    }

    private function recalculateTotal(Order $order)
    {
        $order->update([
            'total_amount' => $order->items()->sum('total')
        ]);
    }

    public function checkout(Order $order)
    {
        abort_if($order->status !== 'draft', 403);

        foreach ($order->items as $item) {
            $item->product->decrement('stock', $item->quantity);
        }

        $order->update([
            'status' => 'printed',
            'printed_at' => now(),
        ]);

        return redirect()->route('orders.print', $order);
    }

    public function complete(Order $order)
    {
        abort_if($order->status !== 'printed', 403);

        $order->update([
            'status' => 'completed'
        ]);

        return redirect()->route('orders.history');
    }




    public function thermalPrint(Order $order)
    {
        $connector = new WindowsPrintConnector("POS-58");
        $printer = new Printer($connector);

        $printer->text("Order #{$order->id}\n");
        $printer->text("---------------------\n");

        foreach ($order->items as $item) {
            $printer->text(
                "{$item->product->name} {$item->quantity}x{$item->price}\n"
            );
        }

        $printer->text("---------------------\n");
        $printer->text("Total: {$order->total_amount}\n");

        $printer->cut();
        $printer->close();
    }


    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }


//    public function destroy(Order $order)
//    {
//        //
//    }
}
