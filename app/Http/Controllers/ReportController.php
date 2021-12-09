<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\HttpStatusCodes;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
// MODEL
use App\User as ModelUser;

class ReportController extends Controller
{

    public function __construct()
    {
        Carbon::setLocale('id');
    }

    private function getNumDayFromTwoDate($dateFrom,$dateTo) {
        $to     = Carbon::parse($dateTo)->addDays(1);
        $from   = Carbon::parse($dateFrom);
        $days   = $to->diffInDays($from);
        return $days;
    }

    private function paginatorCustom($data, $perPage = 5, $page = null, $msg) {
        $page           = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $total          = count($data);
        $currentpage    = $page;
        $perPage        = $perPage ?? 5;
        $offset         = ($currentpage * $perPage) - $perPage ;
        $itemstoshow    = array_slice($data, $offset, $perPage);
        $result         = new LengthAwarePaginator($itemstoshow ,$total ,$perPage);
        return array(
            'status'        => HttpStatusCodes::HTTP_OK,
            'error'         => false,
            'message'       => $msg,
            'data'          => $result->items(),
            'pagination'    => [
                'total'        => $result->total(),
                'count'        => $result->count(),
                'per_page'     => $result->perPage(),
                'current_page' => $result->currentPage(),
                'total_pages'  => $result->lastPage()
            ]
        );
    }

    public function reportMerchantOutletOmzet(Request $term, $merchantID, $outletID) {
        $validator = Validator::make($term->all(), [
            'date_from'    => 'required|date|date_format:Y-m-d',
            'date_to'      => 'required|date|date_format:Y-m-d|after:date_from',
        ]);
        if ($validator->fails()) {
           return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => $validator->errors()->all()[0]
           ],HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $days = self::getNumDayFromTwoDate($term->date_from,$term->date_to);
        if($days > 31) {
          return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => 'Maximum range date in 31 days.'
           ],HttpStatusCodes::HTTP_BAD_REQUEST);  
        }
        $checkMerchant = DB::table('Merchants')
        ->where('user_id',$term->auth_user->id)
        ->where('id',$merchantID)
        ->first();
        if(!$checkMerchant) {
            return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => 'Merchant not found.'
            ],HttpStatusCodes::HTTP_BAD_REQUEST); 
        }
        $checkOutlet = DB::table('Outlets')
        ->where('merchant_id',$checkMerchant->id)
        ->where('id',$outletID)
        ->first();
        if(!$checkOutlet) {
            return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => 'Outlet not found.'
            ],HttpStatusCodes::HTTP_BAD_REQUEST); 
        }
        $period = CarbonPeriod::create($term->date_from, $term->date_to);
        $data   = [];
        foreach($period as $valDate) {
            $dateFormat = date('Y-m-d',strtotime($valDate));
            $getRecord = DB::table('Transactions')
            ->select(DB::raw('SUM(bill_total) as total'))
            ->where('merchant_id',$checkMerchant->id)
            ->where('outlet_id',$checkOutlet->id)
            ->whereDate('created_at',$dateFormat)
            ->groupBy(DB::raw('Date(created_at)'))
            ->first();
            if($getRecord) {
                array_push($data, array(
                    'name_merchant'  => $checkMerchant->merchant_name,
                    'name_outlet'    => $checkOutlet->outlet_name,
                    'omzet'          => $getRecord->total,
                    'date'           => $dateFormat
                ));
            } else {
                array_push($data, array(
                    'name_merchant'  => $checkMerchant->merchant_name,
                    'name_outlet'    => $checkOutlet->outlet_name,
                    'omzet'          => 0,
                    'date'           => $dateFormat
                ));
            }
        }
        $result = self::paginatorCustom($data,$term->limit,$term->page, 'Successfully get list omzet by merchant in one outlet.');
        return response()->json($result,HttpStatusCodes::HTTP_OK);
    }

    public function reportMerchantOmzet(Request $term, $merchantID) {
        $validator = Validator::make($term->all(), [
            'date_from'    => 'required|date|date_format:Y-m-d',
            'date_to'      => 'required|date|date_format:Y-m-d|after:date_from',
        ]);
        if ($validator->fails()) {
           return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => $validator->errors()->all()[0]
           ],HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $days = self::getNumDayFromTwoDate($term->date_from,$term->date_to);
        if($days > 31) {
          return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => 'Maximum range date in 31 days.'
           ],HttpStatusCodes::HTTP_BAD_REQUEST);  
        }
        $checkMerchant = DB::table('Merchants')
        ->where('user_id',$term->auth_user->id)
        ->where('id',$merchantID)
        ->first();
        if(!$checkMerchant) {
            return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => 'Merchant not found.'
            ],HttpStatusCodes::HTTP_BAD_REQUEST); 
        }
        $period = CarbonPeriod::create($term->date_from, $term->date_to);
        $data   = [];
        foreach($period as $valDate) {
            $dateFormat = date('Y-m-d',strtotime($valDate));
            $getRecord = DB::table('Transactions')
            ->select(DB::raw('SUM(bill_total) as total'))
            ->where('merchant_id',$checkMerchant->id)
            ->whereDate('created_at',$dateFormat)
            ->groupBy(DB::raw('Date(created_at)'))
            ->first();
            if($getRecord) {
                array_push($data, array(
                    'name_merchant'  => $checkMerchant->merchant_name,
                    'omzet'          => $getRecord->total,
                    'date'           => $dateFormat
                ));
            } else {
                array_push($data, array(
                    'name_merchant'  => $checkMerchant->merchant_name,
                    'omzet'          => 0,
                    'date'           => $dateFormat
                ));
            }
        }
        $result = self::paginatorCustom($data,$term->limit,$term->page, 'Successfully get list omzet by merchant in all outlet.');
        return response()->json($result,HttpStatusCodes::HTTP_OK);
        

    }
    
    // end class
}
