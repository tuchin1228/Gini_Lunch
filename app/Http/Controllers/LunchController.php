<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LunchController extends Controller
{

    public function getEmployee()
    {
        $result = Employee::all();
        return $result;
    }

    public function getHistory(Request $req)
    {
        $start = $req->start;
        $end = $req->end;
        $searchType = $req->searchType;
        if ($searchType == 0) {
            $result = DB::select("SELECT employee.name,history.*,DATE(history.created_at) as created_at FROM employee
                            LEFT JOIN history
                            on employee.id = history.employeeId
                            WHERE created_at >= '$start' and created_at <= '$end'
                            ORDER BY employee.id
                            ");

        } else if ($searchType == 2) {
            $result = DB::select("SELECT employee.name,history.*,DATE(history.created_at) as created_at FROM employee
                            LEFT JOIN history
                            on employee.id = history.employeeId
                            WHERE created_at >= '$start' and created_at <= '$end' AND history.type = 0
                            ORDER BY employee.id
                            ");

        } else if ($searchType == 1) {
            $result = DB::select("SELECT employee.name,history.*,DATE(history.created_at) as created_at FROM employee
                            LEFT JOIN history
                            on employee.id = history.employeeId
                            WHERE created_at >= '$start' and created_at <= '$end' AND history.type = 1
                            ORDER BY employee.id
                            ");

        }

        return $result;
    }

    public function insertLunch(Request $req)
    {
        $write_data = [];
        $employees = $req->employees;

        foreach ($employees as $employee) {

            if ($employee['subtotal'] > 0) {
                $write_data[] = [
                    'employeeId' => $employee['id'],
                    'value' => $employee['subtotal'],
                    'title' => $employee['title'],
                    'type' => 0, //消費
                    'created_at' => now(),
                ];
            }
            if ($employee['store'] > 0) {
                $write_data[] = [
                    'employeeId' => $employee['id'],
                    'value' => $employee['store'],
                    'title' => '儲值',
                    'type' => 1, //消費
                    'created_at' => now(),
                ];
            }
            $employeeData = Employee::find($employee['id']);
            $total = $employeeData['total'] - (int) $employee['subtotal'] + (int) $employee['store'];
            Employee::find($employee['id'])->update(['total' => $total]);

        }
        History::insert($write_data);
        return ['success' => true];

    }
}
