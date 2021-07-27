<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    private $rules = [
        'salary' => ['required','int'],
        'work_days_month' => ['required','int'],
        'worked_days_month' => ['required','int'],
        'deduction_mzp' => ['required','boolean'],
        'year' => ['required','int'],
        'month' => ['required','numeric','min:1','max:12'],
        'pensioner' => ['required','boolean'],
        'disabled_person' => ['required','boolean'],
        'disabled_person_category' => ['required_if:disabled_person,===,true','numeric','min:1','max:3'],
    ];

    public function index(){
        view('welcome');
    }

    public function getCalculate(request $request){

        $val = Validator::make($request->all(), $this->rules);
        if($val->fails()) return $val->messages();

        $res = self::calculation($request);

//        return $res;
        return response()->json($res);
    }

    public function postCalculate(request $request){
        $val = Validator::make($request->all(), $this->rules);
        if($val->fails()) return $val->messages();

        $res = self::calculation($request);

        $sal = new Salary;
        $sal->salary = $request->salary;
        $sal->work_days_month = $request->work_days_month;
        $sal->worked_days_month = $request->worked_days_month;
        $sal->deduction_mzp = $request->deduction_mzp;
        $sal->year = $request->year;
        $sal->month = $request->month;
        $sal->pensioner = $request->pensioner;
        $sal->disabled_person = $request->disabled_person;
        $sal->disabled_person_category = $request->disabled_person_category;
        $sal->salary_sum = $res->salary_sum;
        $sal->save();

        return response()->json($sal);
    }

    public function calculation($request){
        $mzp = 42500;
        $mrp = 2917;
        $salary = $request->salary;
        $opv = $salary * 10 / 100;
        $vosms = $salary * 2 / 100;
        $osms = $salary * 2 / 100;
        $so = ($salary-$opv) * 3.5 / 100;

        $ipn = self::ipn($salary,$opv,$vosms,$mrp,$mzp,$request->deduction_mzp);

        //Пенсионер с инвалидностью не облагается налогами;
        if($request->pensioner && $request->disabled_person){
            $salary_sum = $salary;
        }

        //Пенсионер облагается лишь ИПН;
        elseif($request->pensioner){
            $salary_sum = $salary-$ipn;
        }

        elseif($request->disabled_person){

            //Инвалид 1 и 2 группы облагается лишь СО; Инвалид 3 группы облагается ОПВ и СО;
            if($request->disabled_person_category == 1 or $request->disabled_person_category == 2){
                $salary_sum = $salary-$so;
            }else{
                $salary_sum = $salary-$so-$opv;
            }

            //Если ЗП у инвалида превысила 882 МРП, он облагается ИПН;
            if($salary>$mrp*882){
                $salary_sum = $salary_sum - $ipn;
            }
        }

        else $salary_sum = $salary - $ipn - $opv - $osms - $vosms - $so;

        $res = new \stdClass();
        $res->ipn = $ipn;
        $res->opv = $opv;
        $res->osms = $osms;
        $res->vosms = $vosms;
        $res->so = $so;
        $res->salary = $salary;
        $res->salary_sum = $salary_sum;

        return $res;
    }

    public function ipn($salary,$opv,$vosms,$mrp,$mzp,$deduction_mzp){
        $kor = 0;

        //Если заработная плата за месяц меньше 25 МРП
        if($salary<$mrp*25){
            $kor = ($salary - $opv - $vosms);

            //при наличии вычета 1МЗП
            if($deduction_mzp){
                $kor = $kor - $mzp;
            }
            $kor = $kor * 90 / 100;
        }

        $ipn = $salary - $opv - $vosms - $kor;

        //при наличии вычета 1МЗП
        if($deduction_mzp){
            $ipn = $ipn - $mzp;
        }

        $ipn = $ipn * 10 / 100;

        return $ipn;
    }
}
