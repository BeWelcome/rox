<h2>MyTravelbook Chat</h2>

<applet codebase="chat" code=IRCApplet.class archive="irc-unsigned.jar,pixx.jar" width="100%" height="500">
<param name="CABINETS" value="irc.cab,securedirc-unsigned.cab,pixx.cab">

<param name="nick" value="<?php echo $nick; ?>">
<param name="alternatenick" value="Anon???">
<param name="name" value="Java User">
<param name="host" value="<?php echo $chat_host; ?>">
<param name="port" value="<?php echo $chat_port; ?>">
<param name="gui" value="pixx">
<param name="asl" value="false">
<param name="useinfo" value="true">
<param name="useidentserver" value="false">
<param name="style:smileys" value="true">
<param name="style:floatingasl" value="false">
<param name="allowdccchat" value="false">
<param name="allowdccfile" value="false">
<param name="style:sourcefontrule1" value="all all Arial 12">
<param name="style:highlightlinks" value="true">
<param name="highlight" value="true">
<param name="pixx:highlightnick" value="true">
<param name="pixx:timestamp" value="true">
<param name="pixx:showconnect" value="true">
<param name="pixx:showchanlist" value="false">
<param name="pixx:showabout" value="false">
<param name="pixx:showhelp" value="false">
<param name="pixx:showclose" value="false">
<param name="pixx:displaychannelmode" value="false">
<param name="pixx:showstatus" value="false">
<param name="pixx:color7" value="FF9900">
<param name="pixx:color8" value="FF0000">
<param name="pixx:color14" value="CC0099">
<param name="pixx:color13" value="00CCFF">
<param name="pixx:showchannelmodeapply" value="false">
<param name="pixx:showchanneltopicchanged" value="false">
<param name="pixx:configurepopup" value="true">
<param name="pixx:popupmenustring1" value="Ignore">
<param name="pixx:popupmenucommand1_1" value="/ignore %1">
<param name="pixx:popupmenustring2" value="Unignore">
<param name="pixx:popupmenucommand2_1" value="/unignore %1">
<param name="pixx:popupmenustring3" value="Profile">
<param name="pixx:popupmenucommand3_1" value="/url <?php echo PVars::getObj('env')->baseuri; ?>user/%1">
<param name="pixx:styleselector" value="true">

<param name="command1" value="join <?php echo $chat_channel; ?>">

</applet>