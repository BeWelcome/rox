# Deprecated

roxlauncher/roxlauncher.php

This file was the bootstrap for the legacy app. It called
EnvironmentExplorer to load config and called RoxFrontRouter to dispatch
requests. This was wrapped in a try/catch and had it own exception
handler. The exception handler now used is in Symfony, and the
RoxFrontRouter is called from LegacyDispatch.
