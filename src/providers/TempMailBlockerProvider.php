<?php
// namespaces
namespace dharmik225\TempMailBlocker\providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Validator;

class TempMailBlockerServiceProvider extends ServiceProvider{

    protected $defer = false;
    protected $message = "Oops! Your email seems to be playing hide and seek. Time to come out! Please enter your real email.";

    public function register(): void
    {

    }

    public function boot(): void
    {
        Validator::extend('tempmail', function ($attribute, $value, $parameters, $validator) {
            list($name, $domain) = explode("@", $value);
            $temp   = explode(".", $domain);
            $tld    = end($temp);
            $path   = realpath(__DIR__ . '/../resources/config/tempmaildomains.txt');

            $cachek = md5_file($path);
            $data   = Cache::rememberForever('CheckTempMail_list_' . $cachek, function () use ($path) {
                return collect(explode("\n", file_get_contents($path)));
            });
            return
            !$data->contains(function($value, $key) use($domain, $tld){
                return ($value == $domain) || ($value == "*.$tld");
            });
        }, Lang::has("validation.tempmail") ? trans("validation.tempmail") : $this->message);
    }

}
?>