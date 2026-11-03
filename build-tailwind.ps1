param(
  [string]$CompilerPath = (Join-Path $PSScriptRoot 'tailwindcss.exe')
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $CompilerPath -PathType Leaf)) {
  $compilerCommand = Get-Command $CompilerPath -ErrorAction SilentlyContinue
  if ($compilerCommand) {
    $CompilerPath = $compilerCommand.Source
  } else {
    throw "Tailwind standalone compiler not found. Pass its path with -CompilerPath."
  }
}

$inputCss = Join-Path $PSScriptRoot 'tailwind.input.css'
$baseOutput = Join-Path $PSScriptRoot 'tailwind.css'
$htmlFiles = Get-ChildItem -LiteralPath $PSScriptRoot -Filter '*.html' | ForEach-Object { $_.FullName }

& $CompilerPath --input $inputCss --output $baseOutput --content $htmlFiles --minify
if ($LASTEXITCODE -ne 0) {
  throw 'Tailwind base stylesheet build failed.'
}

$pages = [ordered]@{
  'home' = 'index.html'
  'networks' = 'networks.html'
  'digital' = 'Digital.html'
  'media' = 'media.html'
  'contact' = 'contact.html'
  'booking' = 'booking.html'
  'terms' = 'terms.html'
}

foreach ($page in $pages.GetEnumerator()) {
  $configPath = Join-Path $PSScriptRoot ("tailwind-configs\tailwind.config." + $page.Key + '.cjs')
  $sourcePath = Join-Path $PSScriptRoot $page.Value
  $outputPath = Join-Path $PSScriptRoot ("tailwind-" + $page.Key + '.css')

  if (-not (Test-Path -LiteralPath $configPath -PathType Leaf)) {
    throw "Tailwind theme config not found: $configPath"
  }

  & $CompilerPath --input $inputCss --output $outputPath --config $configPath --content $sourcePath --minify
  if ($LASTEXITCODE -ne 0) {
    throw "Tailwind stylesheet build failed for $($page.Value)."
  }
}
