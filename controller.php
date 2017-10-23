// This in my store method
        
        $orderItems = $request->orderItems;

        if($orderItems[0]) {
            $obj = json_decode($orderItems[0]);
            // $request->orderItems[0] == "[{"id":"1","qty":"33","exp_date":"2017-10-27"}]"
            foreach($obj as $item) {
                $newOrder->items()->attach($item->id, [
                    'status_id' => $itemStat,
                    'qty' => $item->qty,
                    'exp_date' => $item->exp_date,
                    'unit_price' => $item->unit_price, //to add the price inputted while the user created the order
                ]);
            }
        }
