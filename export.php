<?php

/**
 * Mapeia recursivamente todos os arquivos PHP em um diretório, ignorando a pasta "vendor" e o arquivo "export.php".
 *
 * @param string $directory Caminho do diretório inicial.
 * @param string $excludeFile Nome do arquivo na raiz a ser ignorado.
 * @return array Lista de caminhos dos arquivos PHP encontrados.
 */
function mapPhpFiles(string $directory, string $excludeFile = 'export.php'): array
{
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        // Ignorar a pasta "vendor" e o arquivo de exportação na raiz
        if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) ||
            ($file->getFilename() === $excludeFile && $file->getPath() === $directory)) {
            continue;
        }

        // Verificar se o arquivo tem extensão ".php"
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    // Ordenar: arquivos primeiro, em ordem alfabética, seguido por subpastas
    usort($files, function ($a, $b) {
        $aIsFile = is_file($a);
        $bIsFile = is_file($b);

        if ($aIsFile && !$bIsFile) {
            return -1; // Arquivos antes de pastas
        } elseif (!$aIsFile && $bIsFile) {
            return 1; // Pastas depois de arquivos
        }

        return strcmp($a, $b); // Ordem alfabética
    });

    return $files;
}

/**
 * Gera um arquivo .txt com o conteúdo de todos os arquivos PHP encontrados.
 *
 * @param array $files Lista de arquivos PHP.
 * @param string $outputFile Caminho do arquivo de saída.
 */
function generateFileContentsReport(array $files, string $outputFile): void
{
    // Limpa o conteúdo do arquivo de saída
    file_put_contents($outputFile, '');

    $output = "";

    foreach ($files as $file) {
        $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file); // Caminho relativo
        $fileContent = file_get_contents($file); // Lê o conteúdo do arquivo

        $output .= "#{$relativePath}\n\n"; // Adiciona o caminho relativo
        $output .= "[{$fileContent}]\n\n"; // Adiciona o conteúdo do arquivo entre colchetes
        $output .= "--------------------------------\n\n"; // Separador
    }

    // Grava todo o conteúdo no arquivo de saída
    file_put_contents($outputFile, $output, FILE_APPEND);
}

// Exemplo de uso
$directoryToMap = __DIR__; // Substitua por outro caminho, se necessário.
$outputFile = __DIR__ . '/php_files_report.txt'; // Caminho do arquivo de saída

// Mapeia os arquivos PHP
$phpFiles = mapPhpFiles($directoryToMap);

// Gera o arquivo com o relatório
generateFileContentsReport($phpFiles, $outputFile);

echo "Relatório gerado com sucesso: {$outputFile}\n";
