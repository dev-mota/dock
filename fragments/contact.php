<script type="text/javascript" src="js/contact.js"></script>
<div ng-controller="ContactController">
	<div class="page-container">
		<h1>Contact Us</h1>
		<form>
			<div class="row">
				<div class="form-group col-md-6">
					<label for="name">Name:</label> <input type="text"
						class="form-control" ng-model="contact.userName">
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label for="email">Email:</label> <input type="email"
						class="form-control" ng-model="contact.eMail">
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label for="subject">Subject:</label> <input type="text"
						class="form-control" ng-model="contact.subject">
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label for="message">Message:</label>
					<textarea class="form-control" rows="15" ng-model="contact.message"></textarea>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<div class="g-recaptcha" data-sitekey="6LdzTzAUAAAAALySKFDaqvrNI7nPRlj7an1Nkl3Z" ng-model="contact.captcha"></div>
					<button class="btn btn-default dockthor-button"
						ng-disabled="(contact.userName == null || contact.userName == '') || (contact.eMail == null || contact.eMail == '') || (contact.subject == null || contact.subject == '') || (contact.message == null || contact.message == '') || !captcha"
						ng-click="sendContactMessage()">Send</button>
					<button class="btn btn-default dockthor-button" type="reset"
						ng-click="resetContactMessage()">Reset</button>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<div class="alert alert-success" role="alert" ng-show="success">
						{{message}}
					</div>
					<div class="alert alert-danger" role="alert" ng-show="error">
						{{message}}
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
