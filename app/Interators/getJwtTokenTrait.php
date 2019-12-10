<?php
namespace App\Interactors;

use Illuminate\Http\Request;
use  JWTAuth;

trait getJwtTokenTrait
{
	public function getJwtToken(Request $request)
	{
		$user = JWTAuth::authenticate($request->token);
		return  response()->json(['user' => $user]);
	}
}