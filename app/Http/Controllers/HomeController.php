<?php

namespace App\Http\Controllers;

use App\Models\Entrance;
use App\Models\Invoice;
use App\Models\Output;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use function PHPUnit\Framework\isEmpty;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }



    public function saveStock(Request $request)
    {
        $article = Stock::where('code', $request->code)->first();// provera da li postoji u bazi

        if (isset($article)){
            return redirect()->back()->with('message','Artikal NE MOŽE biti snimljen, jer postoji u bazi podataka!');
        }else {
            $request->validate([
                'code'=>'required',
                'article'=>'required',
                'unit'=>'required',
                'price'=>'required'
            ]);
            $new_stock = new Stock();
            $new_stock->code = $request->code;
            $new_stock->article = $request->article;
            $new_stock->unit = $request->unit;
//        if ($request->purchase_price != null){
//            $new_stock->purchase_price = $request->purchase_price;
//        }
//        if ($request->purchase_price > 0){
//            $new_stock->margin = ($request->price - $request->purchase_price) / $request->purchase_price * 100;
//        }
            $new_stock->price = $request->price;
//        $new_stock->sum = $request->pcs * $request->price;
            $new_stock->save();
            return redirect()->back()->with('message','Novi artikal je snimljen');

        }

    }



    public function editStock($id)
    {
        $article = Stock::find($id);

        return view('home.editStock', compact('article'));
    }

    public function updateStock($id)
    {
        $article = Stock::find($id);
        $pure_data = \request()->validate([
            'code'=>'required',
            'article'=>'required',
            'unit'=>'required',
            'price'=>'required'
        ]);

        $article->code = \request('code');
        $article->article = \request('article');
        $article->unit = \request('unit');
        $article->price = \request('price');
        if ($article->purchase_price == 0){
            $article->margin = 0;
        }else{
            $article->margin = ($article->price / $article->purchase_price -1) * 100;
        }
        $article->margin = round($article->margin, 2);
        $article->sum = $article->pcs * $article->price;

        $article->update();

        //return redirect('/stock/'.$article->id.'/edit/'); // vracanje na istu stranicu
        return redirect()->back()->with('message', 'Artikal je promenjen');

    }
    public function newInvoice()
    {
        $suppliers = Supplier::all();

        return view('home.newInvoiceData', compact('suppliers'));
    }

    public function saveInvoice(Request $request)
    {
        $request->validate([
           'supplier'=>'required',
           'invoicing_date'=>'required',
           'invoice_amount'=>'required',
            'invoice_number'=>'required'
        ]);

        $new_invoice = new Invoice();
        $new_invoice->invoice_number = $request->invoice_number;
        $new_invoice->invoice_amount = $request->invoice_amount;
        $new_invoice->invoicing_date = $request->invoicing_date;
        $new_invoice->supplier_id = $request->supplier;
        $new_invoice->save();

        $suppliers = Supplier::all();
        $invoice = 'Unesi artikle sa fakture';
        $id = Invoice::where('invoice_number', $request->invoice_number)->latest()->first()->id; // ovde je id zadnje fakture

        Session::flash('message','Osnovni podaci su snimljeni');

        return view('home.newInvoiceData', compact('id', 'invoice', 'suppliers'));
        //return redirect(url('/enter-new-quantities'));
    }

    public function showEntranceForm($id)
    {
        $invoice = Invoice::find($id);

        return view('home.showEntranceForm', compact('invoice'));
    }

    public function saveEntrance(Request $request, $id)
    {
        $request->validate([
           'code'=>'required',
           'article'=>'required',
            'pcs'=>'required',
            'purchase_price'=>'required',
            'price'=>'required',
            'sum'=>'required'
        ]);
        $new_entrance = new Entrance();
        $new_entrance->code = $request->code;
        $new_entrance->article = $request->article;
        $new_entrance->pcs = $request->pcs;
        $new_entrance->purchase_price = $request->purchase_price;
        $new_entrance->rebate = $request->rebate;
        $new_entrance->discount = $request->discount;
        $new_entrance->tax = $request->tax;
        $new_entrance->sum = $request->sum;
        $new_entrance->invoice_id = $id;
        $new_entrance->save();

        $stock = Stock::where('code', $request->code)->first();
        $stock->pcs = $stock->pcs + $request->pcs;
        $stock->purchase_price = $request->purchase_price * (1-$request->rebate / 100) * (1-$request->discount / 100) * (1 + $request->tax / 100);
//        $stock->purchase_price_sum = $stock->purchase_price_sum + //dodao 02.12.2024.
//            ($request->purchase_price * (1-$request->rebate / 100) * (1-$request->discount / 100) * (1 + $request->tax / 100) * $request->pcs);
        $stock->purchase_price_sum = $stock->purchase_price * $stock->pcs;//dodao 02.12.2024.

        $stock->price = $request->price;
        $stock->margin = ($stock->price / $stock->purchase_price -1) * 100;
        $stock->margin = round($stock->margin, 2);
        $stock->sum = $stock->pcs * $stock->price;
        $stock->update();

        $entrances = Entrance::where('invoice_id', $id)->get();
        $invoice = Invoice::find($id);
        $total_per_invoice = Entrance::where('invoice_id', $id)->sum('sum');

        return view('home.showEntranceForm', compact('entrances', 'invoice', 'total_per_invoice'));

    }

    public function all_invoices()
    {
        $all_invoices = Invoice::all();
        $suppliers = Supplier::all();

        return view('home.allInvoices', compact('all_invoices', 'suppliers'));
    }

    public function invoice($id)
    {
        $entrances = Entrance::where('invoice_id', $id)->get();
        $invoice = Invoice::find($id);
        $total_per_invoice = Entrance::where('invoice_id', $id)->sum('sum');
        return view('home.showEntranceForm', compact('entrances', 'invoice', 'total_per_invoice'));
    }


    public function saveOutput(Request $request, $search_date)
    {
        $request->validate([
            'code'=>'required',
            'article'=>'required',
            'pcs'=>'required',
            'price'=>'required',
            'sum'=>'required'
        ]);

        $new_output = new Output();
        $new_output->code = $request->code;
        $new_output->article = $request->article;
        $new_output->pcs = $request->pcs;
        $new_output->price = $request->price;
        $new_output->sum = $request->sum;
        $margin = Stock::where('code', $request->code)->first()->margin;
        if ($margin <= 0){
            $new_output->total_profit = round($request->pcs * ($request->price - (($request->price * 100) / (100 + 20))), 2);
        }else{
            $new_output->total_profit = round($request->pcs * ($request->price - (($request->price * 100) / (100 + $margin))), 2);
        }
        $new_output->date_of_turnover = $search_date;
        $new_output->save();

        $stock = Stock::where('code', $request->code)->first();
        $stock->pcs = $stock->pcs - $request->pcs;
        $stock->sum = $stock->price * $stock->pcs;
        $stock->purchase_price_sum = $stock->pcs * $stock->purchase_price;//dodao 02.12.2024.
        $stock->update();

        $search_data = Output::where('date_of_turnover', $search_date)->orderby('id', 'ASC')->get();
        $sum = DB::table('outputs')->where('date_of_turnover', $search_date)
            ->select('sum')->sum('sum'); // dobijamo ukupan promet na trazeni dan

        return view('home.requestedDay', compact('search_data', 'search_date', 'sum'));
    }

    public function turnover_by_days(){
//        $turnover_by_days = DB::table('outputs')
//            ->select('date_of_turnover', DB::raw('SUM(sum) as total'))
//            ->groupBy('date_of_turnover')
//            ->get(); // iz dokumentacije laravel-a - ukupan promet po datumima zajedno grupisano
        return view('home.turnoverByDays');
    }

    public function requestedDay(Request $request) {
        $search_date = $request->date;
        $search_data = Output::where('date_of_turnover', $search_date)->orderBy('id', 'ASC')->get();
        $sum = DB::table('outputs')->where('date_of_turnover', $search_date)
            ->select('sum')->sum('sum'); // dobijamo ukupan promet na trazeni dan
       // session()->flash('from_method', 'requestedDay'); //koristimo za uslov u requestedDay.blade
        session()->put('from_method', 'requestedDay2'); //koristimo za uslov u requestedDay.blade

        return view('home.requestedDay', compact('search_data', 'search_date', 'sum'));

    }

    public function requestedDay2($search_date)
    {
        $search_data = Output::where('date_of_turnover', $search_date)->orderBy('id', 'ASC')->get();
        $sum = DB::table('outputs')->where('date_of_turnover', $search_date)
            ->select('sum')->sum('sum'); // dobijamo ukupan promet na trazeni dan
       // session()->flash('from_method', 'requestedDay2');
        session()->put('from_method', 'requestedDay2'); //koristimo za uslov u requestedDay.blade
        return view('home.requestedDay', compact('search_data', 'search_date', 'sum'));
    }

    public function descendingArticle($search_date)
    {
        $search_data = Output::where('date_of_turnover', $search_date)->orderBy('id', 'DESC')->get();
        $sum = DB::table('outputs')->where('date_of_turnover', $search_date)
            ->select('sum')->sum('sum'); // dobijamo ukupan promet na trazeni dan
       // session()->flash('search_date', $search_date); // Čuvamo datum pretrage
        //session()->flash('from_method', 'descendingArticle');
        session()->put('from_method', 'descendingArticle'); //koristimo za uslov u requestedDay.blade - umesto flash() koristite put() tako da podaci ostanu u sesiji dok se ručno ne obrišu
        return view('home.requestedDay', compact('search_data', 'search_date', 'sum'));
    }

    public function updateBeforeDelete($entrance_id, $code, $invoice_id) // metoda za brisanje ulaza robe
    {
        $delete_article = Entrance::find($entrance_id); // artikal sa svim parametrima koji se brise
        $update_article = Stock::where('code', $code)->first(); // artikal cije se stanje na lageru menja
        $update_article->pcs -= $delete_article->pcs;
        $update_article->sum = $update_article->pcs * $update_article->price;
        $update_article->purchase_price_sum = $update_article->pcs * $update_article->purchase_price; //dodao 02.12.2024.

        $update_article->update();
        $delete_article->delete();
        $entrances = Entrance::where('invoice_id', $invoice_id)->get();

        $total_per_invoice = Entrance::where('invoice_id', $invoice_id)->sum('sum');
        $invoice = Invoice::find($invoice_id);
        //return redirect()->route('requestedDay', ['date'=>$search_date]);
        //return redirect()->back()->with('message', 'Artikal je obrisan iz prometa i vracen ponovo na stanje lagera');
        return view('home.showEntranceForm', compact('entrances', 'invoice', 'total_per_invoice'));

    }

    public function updateBeforeDelete2($id, $search_date) // metoda za brisanje izlaza robe
    {
        $delete_article = Output::find($id); // artikal sa svim parametrima koji se brise
        $update_article = Stock::where('code', $delete_article->code)->first(); // artikal cije se stanje na lageru menja
        $update_article->pcs += $delete_article->pcs;
        $update_article->sum = $update_article->pcs * $update_article->price;
        $update_article->purchase_price_sum = $update_article->pcs * $update_article->purchase_price; //dodao 02.12.2024.
        $update_article->update();

        $delete_article->delete();

        $search_data = Output::where('date_of_turnover', $search_date)->get();
        $sum = DB::table('outputs')->where('date_of_turnover', $search_date)
            ->select('sum')->sum('sum'); // dobijamo ukupan promet na trazeni dan
        //return redirect()->route('requestedDay', ['date'=>$search_date]);
        //return redirect()->back()->with('message', 'Artikal je obrisan iz prometa i vracen ponovo na stanje lagera');

        return view('home.requestedDay', compact('search_data', 'search_date', 'sum'));

    }

    public function totalDebt()
    {
        $suppliers = Supplier::all();

        return view('home.totalDebt', compact('suppliers'));
    }

    public function add_supplier(Request $request)
    {
        $request->validate([
            'supplier'=>'required'
        ]);
        $new_supplier = new Supplier();
        $new_supplier->supplier = $request->supplier;
        $new_supplier->other_data = $request->other_data;
        $new_supplier->save();

        return redirect()->back()->with('message', 'Novi dobavljač je snimljen');
    }

    public function addPayment(Request $request)
    {
        $request->validate([
            'invoice_id'=>'required',
            'supplier_id'=>'required',
            'invoice_payment'=>'required'
        ]);

        $payment = new Payment();
        $payment->invoice_payment = $request->invoice_payment;
        $payment->invoice_id = $request->invoice_id;
        $payment->supplier_id = $request->supplier_id;
        $payment->save();

        $all_invoices = Invoice::all();
        $suppliers = Supplier::all();

        Session::flash('message','Uplata je evidentirana');

        return view('home.allInvoices', compact('all_invoices', 'suppliers'));

    }

    public function editInvoiceData(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        return view('home.editInvoiceData', compact('invoice'));

    }

    public function updateInvoice(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $request->validate([
            'invoice_number'=>'required',
            'invoice_amount'=>'required',
            'invoicing_date'=>'required'
        ]);
        $invoice->invoice_number = $request->invoice_number;
        $invoice->invoice_amount = $request->invoice_amount;
        $invoice->invoicing_date = $request->invoicing_date;
        $invoice->update();

        return redirect()->back()->with('message', 'Podaci su promenjeni');
    }

    public function searchStock(Request $request)
    {
        $request->validate([
            'name'=>'required'
        ]);
        $all_stocks = Stock::all();
        $request = $request->name;
        $stock_exists = Stock::where('code', 'LIKE', $request)->exists();
        $name_exists = Stock::where('article', 'LIKE', '%'.$request.'%')->exists();
        if ($stock_exists && $request != "") {
            $search_stocks = Stock::where('code', 'LIKE', $request)->get();
            return view('home.stock', compact('search_stocks', 'all_stocks'));
        }elseif ($name_exists && $request != ""){
            $search_stocks = Stock::where('article','LIKE','%'.$request.'%')->get();//carobna linija koda
            return view('home.stock', compact('search_stocks', 'all_stocks'));
        }elseif ($stock_exists == false || $name_exists == false || $request == "") {
            return view('home.stock', compact('all_stocks'));
        }

    }

    public function supplier_invoices($id)
    {
        $supplier_invoices = Invoice::where('supplier_id', $id)->get();
        $supplier = Supplier::find($id);

        return view('home.supplierInvoices', compact('supplier_invoices', 'supplier'));

    }

    public function getMonth($id)
    {
        $turnover_by_days = Output::select(DB::raw('DATE(date_of_turnover) as day'), DB::raw('SUM(sum) as total'))
            ->whereMonth('date_of_turnover', $id)
            ->groupBy('day')
            ->get(); // carobna linija koda - dobijam sabran promet za svaki dan tokom odabranog meseca

        return view('home.turnoverByDays', compact('turnover_by_days'));

    }

    public function invoice_payment($id)
    {
        $invoice = Invoice::find($id);
        $payments = Payment::where('invoice_id', $id)->get();
        $supplier_invoices = Invoice::where('supplier_id', $invoice->supplier_id)->get();
        $supplier = Supplier::find($invoice->supplier_id);
        return view('home.supplierInvoices', compact('invoice', 'payments', 'supplier_invoices', 'supplier'));
    }



}
