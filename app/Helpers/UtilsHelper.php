<?php

use App\Models\Balance;

if (!function_exists('currency_format')) {
    function currency_format(?float $value)
    {
        if ($value === null || empty($value)) {
            return  '$0,00';
        }
        return '$'. number_format($value, 2, ',', '');
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat(?DateTimeInterface $value, $format = "d/m/Y H:i:s"):string
    {
        if ($value === null) {
            return '';
        }
        return  $value->format($format);
    }
}


if (!function_exists('money_format_bd')) {
    function money_format_bd($value)
    {
        if ($value === null || empty($value)) {
            return '0.00';
        }

        $value = preg_replace("/[^0-9,\"{}:]/", "", $value);

        $source = array('.', ',');
        $replace = array('', '.');
        $value = str_replace($source, $replace, $value);
        return $value;
    }
}

if (!function_exists('status_format')) {
    function status_format($str)
    {
        switch ($str) {
            case 'A':
                return 'APPROVED';
                break;

            case 'P':
                    return 'PENDING';
                break;
            case 'R':
                    return 'REJECT';
                break;
        }
    }
}


if (!function_exists('resume_balance')) {
    function resume_balance($user, $format=false)
    {
        $data = Balance::select('id', 'amount', 'type', 'status', 'created_at')
                                ->where('account_id', $user->account->id)->get();
        $check   = $data->where('type', 'C')
                        ->where('status', 'A')
                        ->sum('amount');
        $expense   = $data->where('type', 'E')->sum('amount');
        $current = ($check - $expense);

        return (object)array(
                             "check"=> $format ? currency_format($check) : $check,
                             "expense"=> $format ? currency_format($expense) : $expense,
                             "current"=> $format ? currency_format($current) : $current
                            );
    }
}
