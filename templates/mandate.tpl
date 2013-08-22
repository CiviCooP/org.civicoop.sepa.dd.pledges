{literal}
<style type="text/css">
td { 
	vertical-align: top;
}
th {
	font-weight: bold;
	text-align: left;
	vertical-align: top;
	width: 200px;
}
div.footer {
	font-size: 0.9em;
	font-style: italic;
}
</style>
{/literal}

<h1>SEPA Mandaat (machtinging doorlopende incasso)</h1>

<p><em>Logo incassant</em></p>

<div>
	<table>
		<tr><th>Naam incassant</th><td>{$creditor_name}</td></tr>
		<tr><th>Adres incassant</th><td>{$creditor_address}</td></tr>
		<tr><th>Incassant ID</th><td>{$creditor_id}</td></tr>
		<tr><th>Mandaat ID (kenmerk machtiging)</th><td>{$mandaat_id}</td></tr>
	</table>
</div>

<div>
	<p>Door ondertekening van dit formulier geeft u toestemming aan:</p>
	<ul>
		<li><em>{$creditor_name}</em> om doorlopend incasso-opdrachten te sturen naar uw bank om een bedrag van uw rekening af te schrijven en</li>
		<li>uw bank om doorlopend een bedrag van uw rekening af te schrijven overeenkomstig de opdracht van <em>{$creditor_name}</em></li>
	</ul>
	<p>Als u het niet eens bent met deze afschrijving kunt u deze laten terugboeken. Neem hiervoor binnen acht weken na afschrijving contact op met uw bank. Vraag uw bank naar de voorwaarden.</p>
</div>

<div>
	<table>
		<tr><th>Naam en voorletters</th><td>{$contact.display_name}</td></tr>
		<tr><th>Adres</th><td>{$contact.street_address}<br />{$contact.postal_code} {$contact.city}<br />{$contact.country}</td>
		<tr><th>IBAN</th><td>{$iban}</td></tr>
		
		<tr><td><br /><strong>Plaats en Datum</strong></td><td><br /><strong>Handtekening</strong></td></tr>
		<tr><td><br /><br /><br />...............</td><td><br /><br /><br />..................</td></tr>
	</table>
</div>

<div class="footer">
<p>Onderteken dit formulier en stuur het terug naar {$creditor_name}.</p>
</div>
			
				
			