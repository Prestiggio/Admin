<?php namespace Ry\Admin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\User;
use Auth;
use Illuminate\Database\Eloquent\Model;

class UserZero extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'ryadmin:user0';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Creer un utilisateur zero avec role admin.';

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
	public function handle()
	{
		$use_trait = array_has(class_uses(User::class), "Ry\Admin\Models\Traits\AdministratorTrait");
		
		while(!$use_trait) {
			if(!$this->confirm("Ampio trait Ry\Admin\Models\Traits\AdministratorTrait aloha le user e")) {
				$this->warn("Tsy afaka asina role zany ny user an");
				break;
			}
			$use_trait = array_has(class_uses(User::class), "Ry\Admin\Models\Traits\AdministratorTrait");
			if(!$use_trait) {
				$this->error("Mbol ts hita ian");
			}
		}
		
		$credentials = [
				"name" => $this->ask("Nom:"),
				"email" => $this->ask("Email:"),
				"password" => $this->secret("Mot de passe:"),
		        "guard" => $this->choice("Interface:", ["admin", "manager"], "admin")
		];
		
		$confirmation = $this->secret("Confirmer le mot de passe:");
		while ($credentials["password"]!=$confirmation) {
			$this->error("Les mots de passe ne correspondent pas !");
			$credentials["password"] = $this->secret("Mot de passe:");
			$confirmation = $this->secret("Confirmer le mot de passe:");
		}
		
		$user = User::where("email", "=", $credentials["email"]);
		while($user->exists()) {
			$this->error("L'utilisateur existe déjà !");
			$credentials["email"] = $this->ask("Email:");
			$user = User::where("email", "=", $credentials["email"]);
		}
		
		$user = User::create([
			"name" => $credentials["name"],
			"email" => $credentials["email"],
			"password" => bcrypt($credentials["password"]),
		    "guard" => $credentials['guard'],
		    "active" => true
		]);
		$user->roles()->updateOrCreate([
					"name" => "admin"
	    ]);
		
		return $this->info("Vous etes passe au role d'administrateur - Merci :)");
	}

}
