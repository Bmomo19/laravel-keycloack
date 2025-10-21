<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait AgenceTrait
{

    public function getUserAgence()
    {
        try {
            $query = "SELECT * FROM dbo.f_GetAgencesUser ('" . $request->userlogin . "') Order By codagence";
            $fromSqlSrv = DB::connection('sqlsrv')->select($query);
            if (empty($fromSqlSrv)) {
                return json_decode(json_encode([
                    'codagence' => 'all',
                    'nomagence' => 'TOUTES LES AGENCES',
                    'Loginuser' => $request->userLogin
                ]), true);
            } else {
                $findUser = User::where('userLogin', $request->userlogin)->first();
                if (isset($findUser) && $findUser->codeGroupeUser !== "DAG" && $findUser->codeGroupeUser !== "GEST" && $findUser->codeGroupeUser !== "userGestDag") {
                    array_unshift($fromSqlSrv, [
                        'codagence' => 'all',
                        'nomagence' => 'TOUTES LES AGENCES',
                        'Loginuser' => $request->userLogin
                    ]);
                    return $fromSqlSrv;
                } else {
                    return $fromSqlSrv;
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}
