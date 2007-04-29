{include file="header.tpl"}

<script type="text/javascript" src="{$wwwroot}/libs/jquery/jquery-1.1.2.pack.js"></script>
<script type="text/javascript" src="{$wwwroot}/libs/jquery/interface_1.2/interface.js"></script>
<script type="text/javascript" src="{$wwwroot}/libs/jquery/jquery.inplace.js"></script>
<script type="text/javascript" src="{$wwwroot}/libs/jquery/jquery.thickbox2.js"></script>

<script type="text/javascript" src="{$themeroot}/scripts/viewset.js"></script>


<div id="content">
	<div id="viewset_nameDiv"><span class="viewset_setName">My Sets</span></div>
	
	<div id="inner_content">
		{section name=set loop=$sets}
		<div class="viewset_mySetTeaser">
			<img src="{$thumbs}/{$sets[set].setThumb}"/>
			<h3><a href="viewset.php?sid={$sets[set].sid}">{$sets[set].setName}</a> ({$sets[set].objectCount})</h3>
			<p>{$sets[set].setDescription}</p>
			<a href="viewset.php?sid={$sets[set].sid}">View</a> | <a href="shareSet.php?height=270&width=400&sid={$sets[set].sid}" class="thickbox" title="Share this set">Share</a>  | <a href="#" title="">Delete</a> 
		</div>
		{/section}
	</div>
</div>

{include file="footer.tpl"}