<form action="/insert_user" name="frm_user" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-user-plus"></i> Nouvel utilisateur</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<div class="row">
				<div class="col-4 text-center">
    				<img src="noimage.jpg" class="img-fluid img-thumbnail rounded-circle" style="width: 160px; height: 160px; object-fit: cover;"/>
    				<input type="file" name="photo" id="photo" class="d-none"/>
    				<label for="photo" class="bg-primary mouse-pointable mt-3 p-4 rounded text-white">@lang("Choisir une photo")</label>
    			</div>
    			<div class="col-8">
    				<div class="row">
    					<div class="form-group col-2">
    						<label for="profile-gender">@lang("Civilité")</label>
							<select name="profile[gender]" class="form-control" id="profile-gender">
  								<option value="M">Mr.</option>
  								<option value="Mme">Mme.</option>
  								<option value="Mlle">Mlle.</option>
							</select>
  						</div>
  						<div class="form-group col-5">
                            <label for="profile-firstname">@lang("Prénom")</label>
                            <input type="text" name="profile[firstname]" class="form-control" id="profile-firstname"/>
                        </div>
                        <div class="form-group col-5">
                            <label for="profile-lastname">@lang("Nom")</label>
                            <input type="text" name="profile[lastname]" required class="form-control" id="profile-lastname"/>
                        </div>
    				</div>
    				<div class="card">
    					<div class="card-header">
    						<i class="fa fa-shield-alt"></i> @lang("Informations & statuts")
    					</div>
    					<div class="card-body">
    						<div class="row">
    							<div class="form-group col-6">
                                    <label for="contacts-0-coord">@lang("Téléphone")</label>
                                    <input type="text" class="form-control" id="contacts-0-coord" name="contacts[0][coord]"/>
                                    <input type="hidden" name="contacts[0][contact_type]" value="phone"/>
                                    <input type="hidden" name="contacts[0][type]" value="fixe"/>
                                </div>
                                <div class="form-group col-6">
                                    <label for="contacts-1-coord">@lang("Mobile")</label>
                                    <input type="text" class="form-control" id="contacts-1-coord" name="contacts[1][coord]"/>
                                    <input type="hidden" name="contacts[1][contact_type]" value="phone"/>
                                    <input type="hidden" name="contacts[1][type]" value="mobile"/>
                                </div>
    						</div>
    					</div>
    				</div>
    				<div class="card mt-4">
    					<div class="card-header">
    						<i class="fa fa-lock"></i> @lang("Informations de connexion")
    					</div>
    					<div class="card-body">
    						<div class="row">
    							<div class="form-group col-6">
                                    <label for="email">@lang("Email")</label>
                                    <input type="email" class="form-control" id="email" name="email"/>
                                </div>
                                <div class="form-group col-6">
                                    <label for="password">@lang("Mot de passe")</label>
                                    <input type="password" class="form-control" id="password" name="password"/>
                                </div>
    						</div>
    					</div>
    				</div>
    			</div>
			</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
</form>