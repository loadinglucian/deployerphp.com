<p align="center">
    <a href="https://github.com/loadinglucian/deployer-php" target="_blank">
        <img src="https://raw.githubusercontent.com/loadinglucian/deployer-php/main/docs/images/logo-mark.svg" width="400" alt="DeployerPHP Logo">
    </a>
</p>

# DeployerPHP Documentation Site

This is the documentation site for [DeployerPHP](https://github.com/loadinglucian/deployer-php), a complete set of CLI tools for provisioning, installing, and deploying servers and sites using PHP.

It is a Laravel + Livewire application that renders the DeployerPHP documentation as a beautiful, browsable website. The documentation content itself is not stored in this repository — it is read at runtime from the `docs` folder of the `loadinglucian/deployer-php` Composer dependency.

> [!NOTE]
> This site is no longer hosted online. Clone this repository and run it locally to browse the documentation as a website. Alternatively, you can read the markdown files directly in the [deployer-php docs folder](https://github.com/loadinglucian/deployer-php/tree/main/docs).

## Requirements

- PHP 8.5+ and Composer
- Node.js and npm (for building assets)
- A [Flux Pro](https://fluxui.dev) license — the UI depends on `livewire/flux-pro`, which requires Composer credentials for `composer.fluxui.dev` (see the [Flux installation docs](https://fluxui.dev/docs/installation))

## Running Locally

```shell
git clone https://github.com/loadinglucian/deployerphp.com.git
cd deployerphp.com

# Install dependencies, create .env, generate app key,
# run migrations, and build frontend assets
composer run setup

# Start the local dev servers (app, queue, logs, vite)
composer run dev
```

Then open <http://localhost:8000> to browse the documentation. A command reference is available at <http://localhost:8000/command-index>.

## Previewing Local Documentation Changes

By default, the site renders the documentation shipped with the `loadinglucian/deployer-php` Composer package (`vendor/loadinglucian/deployer-php/docs`). To preview documentation changes from a local checkout instead, point `DOCS_PATH` at it in your `.env`:

```dotenv
DOCS_PATH=/path/to/deployer-php/docs
```
