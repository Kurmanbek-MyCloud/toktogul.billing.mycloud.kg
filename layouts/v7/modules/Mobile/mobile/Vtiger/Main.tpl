{include file="../Header.tpl" scripts=$_scripts}
    <div id="app">
        <router-view></router-view>
        <notifications position="bottom right" group="warn"/>

        {*<notifications group="notification" position="top right" classes="vue-notification-custom"/>*}
    </div>
{include file="../Footer.tpl"}
