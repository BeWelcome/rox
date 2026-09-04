# DevContainer

This directory configures a VS Code [Dev Container](https://containers.visualstudio.com/docs/devcontainer/) for local Rox development. See [INSTALL.md](../INSTALL.md) for full setup instructions.

## Quick reference

| Service | Address |
|---|---|
| BeWelcome app | `http://localhost` |
| Mailpit (catch-all mailer) | `http://localhost:1080` |
| Manticore HTTP API | `http://localhost:9308` |
| Manticore MySQL protocol | `localhost:9306` |

Default login: `member-2` / `password`

## macOS + Rancher Desktop

If pages hang when accessing `localhost:1080` or `localhost:80`, remove the `forwardPorts` key from `devcontainer.json` and rebuild the container (**Ctrl+Shift+P → Dev Containers: Rebuild Container**). Rancher Desktop uses SSH tunnelling to expose container ports, which conflicts with VS Code's additional port-forwarding layer. Removing `forwardPorts` lets Rancher handle it directly. This does not affect WSL or Docker Desktop.

## GitHub Codespaces

Open the repository on GitHub and choose **Code → Codespaces → Create codespace on master** to run the full stack in the cloud without any local installation.
