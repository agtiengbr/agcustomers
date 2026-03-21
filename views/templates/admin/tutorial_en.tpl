<h2> Creating the Application </h2>
<p> To use the module, you must create an application with Facebook. It is through the application that the module obtains access to the name, surname and e-mail address of its clients. Do not worry, the creation is quite simple, and will be explained in this guide. If you already have an app, you can skip right to the next step. </p>

<p> First, visit the <a href="https://developers.facebook.com/apps/" target="_blank"> Facebook developer site </a>. Then click "Create an Application." </p>

<center>
<img class = "img-thumbnail" src = "{$img01}" alt = "Facebook Application Creation" />
<img class = "img-thumbnail" src = "{$img02}" alt = "Facebook Application Creation" />
</center>

<p> In the application name, it is recommended to use the name of your PrestaShop store. </p>

<h2> Apliative Configuration </h2>

<p>
You must now enable the social login features in the application you have already created. To do so, in the "Add a Product" menu, click "Facebook Login", and click "Configure".
</p>

<center>
<img class="img-thumbnail" src="{$img03}" alt="Social Login Setup by Facebook" />
</center>

<p> Now, choose the WEB platform. In the Site URL field, enter the domain of your store and click Save. Now in the left menu, click Settings, uncheck the option "Use strict mode for redirect URIs" and click "Save". In the "Valid OAuth redirect URIs" field, fill in with the value {$redirectLink}. </ P>

<center>
<img class = "img-thumbnail" src = "{$img04}" alt="Social Login Setup by Facebook" />
</center>

<p> Finally, in the "Panel" menu, you should copy the APP ID, Secret Key and API Version parameters, and configure these parameters in the "Configuration" tab of the module. </p>