<?php namespace Ry\Admin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\User;
use Auth;
use Illuminate\Database\Eloquent\Model;

class Admin extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'ryadmin:adduser';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Assigner un compte existant a administrateur en mettant en parametre le login email.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$credentials = [
				"email" => $this->ask("Login:"),
				"password" => ""
		];		
		$user = User::where("email", "=", $credentials["email"]);
		if(!$user->exists())
			return $this->error("L'utilisateur n'existe pas !");
		$credentials["password"] = $this->secret("mot de passe:");
		
		if(Auth::attempt($credentials, false, false)) {
			$me = $user->first();
			if($me->isAdmin())
				return $this->info("Vous etes deja administrateur. Merci :)");
			
			Model::unguard();
			$me->roles()->create([
					"name" => "admin"
			]);
			Model::reguard();
			
			return $this->info("Vous etes passe au role d'administrateur - Merci :)");
		}
		
		$this->error("Login ou mot de passe incorrect ou absent !");
	}

}
