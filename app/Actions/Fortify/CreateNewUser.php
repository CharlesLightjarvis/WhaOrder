<?php

namespace App\Actions\Fortify;

use App\Actions\Merchants\DetectMerchantDefaults;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private readonly Request $request,
        private readonly DetectMerchantDefaults $detectMerchantDefaults,
    ) {}

    /**
     * Validate and create a newly registered user, along with the merchant
     * they'll be running through WhaOrder.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'business_name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $defaults = $this->detectMerchantDefaults->handle($this->request->ip());

            $merchant = Merchant::create([
                'name' => $input['business_name'],
                'currency' => $defaults['currency'],
                'timezone' => $defaults['timezone'],
            ]);

            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'merchant_id' => $merchant->id,
            ]);
        });
    }
}
