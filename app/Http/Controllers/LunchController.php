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
        $employee_total = [];
        $employees = $req->employees;
        $msg = '';
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

            $employee_total[] = $employeeData['name'] . "的餘額有" . $total;
            $subtotal = $employee['subtotal'] > 0 ? "消費 " . $employee['subtotal'] . "元 ," . PHP_EOL : '';
            $store = $employee['store'] > 0 ? "儲值 " . $employee['store'] . "元 ," . PHP_EOL : '';
            $remain = "剩餘：" . $total . "元";
            $msg .= PHP_EOL . $employeeData['name'] . PHP_EOL . $subtotal . $store . $remain . PHP_EOL . '－－－－－－－－－－－－－－';
        }
        History::insert($write_data);
        $this->LineNotify($msg);
        // return [$write_data, $employee_total];
        return ['success' => true];

    }

    public function LineNotify($msg)
    {
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . env('LINE_NOTIFY_TOKEN'),
        );
        $url = 'https://notify-api.line.me/api/notify';

        $body = array(
            'message' => $msg, //先斷行，避免跟 Bot 稱呼黏在一起
        );

        $ch = curl_init();

        $params = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($body),
        );

        curl_setopt_array($ch, $params);

        if (!$result = curl_exec($ch)) {
            if ($errno = curl_errno($ch)) {
                $error_message = curl_strerror($errno);
                // 敵八個用
                echo "cURL error ({$errno}):\n {$error_message}";
                curl_close($ch);
                return false;
            }
        } else {
            curl_close($ch);
            return true;
        }

    }

}
