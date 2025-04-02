<?php

if (!isset($argv[1])) {
    echo "Necessario informar pelo menos um diretorio onde estão as NFs" . PHP_EOL
        . "Exemplo: /home/topedindo/Documents/nfs/" . PHP_EOL;
}

foreach ($argv as $i => $dir) {
    if ($i == 0) {
        continue;
    }

    if (!is_dir($dir)) {
        echo "Diretorio {$dir} não encontrado" . PHP_EOL;
        exit;
    }

    $dirIterator = new DirectoryIterator($dir);
    if (!is_dir($dir . 'ok')) {
        mkdir($dir . 'ok');
    }

    if (!is_dir($dir . 'erro')) {
        mkdir($dir . 'erro');
    }

    foreach ($dirIterator as $file) {
        if ($file->isFile()) {
            //get text inside <qrCode>the text</qrCode>
            $content = file_get_contents($file->getPathname());
            preg_match_all('/<qrCode>(.*?)<\/qrCode>/', $content, $matches);
            if (isset($matches[1])) {
                // open link in $matches[1][0] and check if url has the string "https://sat.sef.sc.gov.br/tax.NET/Sat.DFe.NFCe.Web/Consultas/NFCe_Detalhes.aspx"
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $matches[1][0]);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $result = curl_exec($ch);
                $url = "https://sat.sef.sc.gov.br/tax.NET/Sat.DFe.NFCe.Web/Consultas/NFCe_Detalhes.aspx";
                // check if result contains the url
                $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
                echo $last_url . PHP_EOL;
                echo $url . PHP_EOL;

                if (str_contains($last_url, $url)) {
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        rename($dir . $file->getFilename(), $dir . 'ok\\' . $file->getFilename());
                    } else {
                        rename($dir . $file->getFilename(), $dir . 'ok/' . $file->getFilename());
                    }
                } else {
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        rename($dir . $file->getFilename(), $dir . 'erro\\' . $file->getFilename());
                    } else {
                        rename($dir . $file->getFilename(), $dir . 'erro/' . $file->getFilename());
                    }
                }

                curl_close($ch);
            }
        }
    }
}
