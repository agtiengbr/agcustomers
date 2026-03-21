<h2>Criação do Aplicativo</h2>
<p>Para a utilização do módulo, você deve criar um aplicativo junto ao Facebook. É através do aplicativo que o módulo obtém acesso ao nome, sobrenome e endereço de e-mail de seus clientes. Não se preocupe, a criação é bastante simples, e será explicada neste guia. Se você já possuir um aplicativo, pode ir direto para a próxima etapa.</p>

<p>Primeiramente, acesse o <a href="https://developers.facebook.com/apps/" target="_blank">site de desenvolvedores do Facebook</a>. A seguir, clique em "Criar um Aplicativo".</p>

<center>
	<img class="img-thumbnail" src="{$img01}" alt="Criação de Aplicativo pelo Facebook" />
	<img class="img-thumbnail" src="{$img02}" alt="Criação de Aplicativo pelo Facebook" />
</center>

<p>No nome do aplicativo, é recomendável utilizar o nome de sua loja PrestaShop.</p>

<h2>Configuração do Aplicativo</h2>

<p>
	Agora, é preciso ativar os recursos de login social no aplicativo já criado. Para isso, no menu "Adicionar um Produto", clique em "Login do Facebook", e clique em "Configurar".
</p>

<center>
	<img class="img-thumbnail" src="{$img03}" alt="Configuração do Login Social pelo Facebook" />
</center>

<p>No campo "URIs de redirecionamento do OAuth válidos", preencha com os seguintes valores:
	<ul>
		{foreach from=$redirectLinks item=redirectLink}
			<li>{$redirectLink}</li>
		{/foreach}
	</ul>
</p>

<center>
	<img class="img-thumbnail" src="{$img04}" alt="Configuração do Login Social pelo Facebook" />
</center>

<p>No menu "Configurações" -> "Básico", no campo "Domínios do aplicativo", preencha com o valor {$shop_url}.</p>

<p>Por fim, no menu "Painel", você deve copiar os parâmetros APP ID, Chave Secreta e Versão da API, e configurar estes parâmetros na aba "Configuraçao" do módulo.</p>

 {* Se você ainda não tiver criado um aplicativo, será necessário criá-lo. Caso contrário, apenas clique sobre o aplicativo desejado. A seguir, no painel de controle de seu aplicativo, você visualizará o ID do Aplicativo, a Chave Secreta e a Versão da API.</p> *}