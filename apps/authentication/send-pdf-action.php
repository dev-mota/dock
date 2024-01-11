<?php 
if ((isset ( $_FILES ['pdfFile'] ))) {
	
		//seta diretorio onde serao armazenados os arquivos
		$uploaddir = 'docs/pendding-user-accounts/';
		$uploadfile = $uploaddir . basename($_FILES['pdfFile']['name'][0]);
		
		//cria nome final para o arquivo
		$temp = explode(".", $_FILES["pdfFile"]["name"][0]);
		$newfilename = round(microtime(true)) . '.' . end($temp);


		echo '<pre>';

		if(end($temp) == "pdf") {
			if (move_uploaded_file($_FILES['pdfFile']['tmp_name'][0], $uploaddir.$newfilename)) {
				//echo "Arquivo válido e enviado com sucesso.\n";
			} else {
				//echo "Possível ataque de upload de arquivo! $uploadfile \n";
			}
		
	
			$_SESSION['PDF_SENT']=true;
			header ( "Location: ../../index.php?PDF=true" );
		} else {
			$_SESSION['PDF_SENT']=false;
			header ( "Location: ../../index.php?NOPDF=true" );
		}
	
}
?>
