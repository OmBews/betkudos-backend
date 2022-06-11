<?php

namespace App\Blockchain;

use Exception;
use Illuminate\Support\Facades\App;

class CryptoWallet
{
    private $bitcoin_user = 'bitcoin';
    private $bitcoin_pass = '6QDJ1oJDiIaZOIMelfpO';
    private $bitcoin_host = '45.142.201.217';
    private $bitcoin_port = 5001;

    private $bitcoin_test_host = '45.142.201.217';
    private $bitcoin_test_port = 5003;

    private $eth_password = '6QDJ1oJDiIaZOIMelfpO';
    private $ethereum_host = '188.166.151.138';
    private $ethereum_port = 5002;
    public $eth_hot_wallet = '0x9e2cc1008054256378bddbe738a1b9b2c0c33756';
    public $cwerror = null;

    public function create_address($currency)
    {
        try {
            if ($currency == "btc") {
                
                if (App::environment('production')) {
                    $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_host, $this->bitcoin_port);
                } else {
                    $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_test_host, $this->bitcoin_test_port);
                }
                
                $address = $btc->getnewaddress();

                if ($address === false) {
                    $this->cwerror = $btc->error;
                }

                return $address;

            } else {
                $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
                $address = $eth->personal_newAccount($this->eth_password);
                if ($address === false) {
                    $this->cwerror = $eth->error;
                }
                return $address;
            }
        } catch (Exception $e) {
            $this->cwerror = $e->getMessage();
            return false;
        }
    }


    public function create_erc20_transaction($from, $to, $amount, $contract_address = '0xb404c51bbc10dcbe948077f18a4b8e553d160084', $decimals = 6)
    {
        $eth_data = '0xa9059cbb' . str_pad(substr($to, 2), 64, 0, STR_PAD_LEFT) . str_pad(dechex(intval(($amount) * pow(10, $decimals))), 64, 0, STR_PAD_LEFT);
        return $eth_data;
    }

    public function get_erc20_balance($from, $contract_address = '0xb404c51bbc10dcbe948077f18a4b8e553d160084', $decimals = 6)
    {
        $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
        $message = new EthereumMessage($contract_address, "0x70a08231" . str_pad(substr($from, 2), 64, 0, STR_PAD_LEFT), "0x0000000000000000000000000000000000000000");
        //return $eth->decode_hex($eth->eth_call($message,'latest'));
        $balance = $eth->eth_call($message, 'latest');
        if ($balance === false) {
            $this->cwerror = $eth->error;
            return false;
        } else {
            return hexdec($balance);
        }
    }

    public function get_balance($currency, $address = '')
    {
        if ($currency == "btc") {
            if (App::environment('production')) {
                $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_host, $this->bitcoin_port);
            } else {
                $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_test_host, $this->bitcoin_test_port);
            }

            $balance = $btc->getbalance();

            if ($balance === false) {
                $this->cwerror = $btc->error;
                return false;
            } else {
                return $balance;
            }
        } else {

            $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
            $balance = $eth->eth_getBalance($this->eth_hot_wallet, 'latest', TRUE);
            if ($balance === false) {
                $this->cwerror = $eth->error;
                return false;
            } else {
                return $balance / (pow(10, 18));
            }
        }
    }

    public function get_btc_transaction($txid)
    {
        if (App::environment('production')) {
            $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_host, $this->bitcoin_port);
        } else {
            $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_test_host, $this->bitcoin_test_port);
        }
        $tx_info = $btc->gettransaction($txid);
        if ($tx_info === false) {
            $this->cwerror = $btc->error;
            return false;
        } else {
            return $tx_info;
        }
    }


    public function get_current_block($currency)
    {
        if ($currency == 'btc') {
            if (App::environment('production')) {
                $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_host, $this->bitcoin_port);
            } else {
                $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_test_host, $this->bitcoin_test_port);
            }
            $block_number = $btc->getblockcount();
            if ($block_number === false) {
                $this->cwerror = $btc->error;
                return false;
            } else {
                return $block_number;
            }
        } else {
            $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
            $block_number = $eth->eth_blockNumber();
            if ($block_number === false) {
                $this->cwerror = $eth->error;
                return false;
            } else {
                return $block_number;
            }
        }
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function get_block($currency, $block_number)
    {
        if ($currency == 'btc') {
            if (App::environment('production')) {
                $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_host, $this->bitcoin_port);
            } else {
                $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_test_host, $this->bitcoin_test_port);
            }
            $block_hash = $btc->getblockhash($block_number);

            if ($block_hash === false) {
                $this->cwerror = $btc->error;
                return false;
            } else {
                $block_info = $btc->getblock($block_hash);
                if ($block_info === false) {
                    $this->cwerror = $btc->error;
                    return false;
                } else {
                    return $block_info;
                }
            }
        } else {
            $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
            $block_info = $eth->eth_getBlockByNumber('0x' . dechex($block_number));
            if ($block_info === false) {
                $this->cwerror = $eth->error;
                return false;
            } else {
                return $block_info;
            }
        }
    }


    public function send_raw_transaction($hex)
    {
        if (App::environment('production')) {
            $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_host, $this->bitcoin_port);
        } else {
            $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_test_host, $this->bitcoin_test_port);
        }
        $tx_info = $btc->sendrawtransaction($hex);

        if ($tx_info === false) {
            $this->cwerror = $btc->error;
            return false;
        } else {
            return $tx_info;
        }
    }

    public function encrypt($plaintext, $key)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '9645245079272636';
        $encryption = openssl_encrypt($plaintext, $ciphering, $key, $options, $encryption_iv);
        return $encryption;
    }

    public function decrypt($encryptedtext, $key)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '9645245079272636';
        $decryption = openssl_decrypt($encryptedtext, $ciphering, $key, $options, $encryption_iv);
        return $decryption;
    }

    public function decrypt_and_broadcast($encryptedtext, $key)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '9645245079272636';
        $decryption = openssl_decrypt($encryptedtext, $ciphering, $key, $options, $encryption_iv);
        return $this->send_raw_transaction($decryption);
    }

    public function eth_get_topics($block_number, $topics = ['0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'], $contract_address = '0xb404c51bbc10dcbe948077f18a4b8e553d160084')
    {
        $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
        $eth_filter = new EthereumFilter('0x' . dechex($block_number), '0x' . dechex($block_number), $contract_address, $topics);

        $eth_logs = $eth->eth_getLogs($eth_filter);

        if ($eth_logs === false) {
            $this->cwerror = $eth->error;
            return false;
        } else {
            return $eth_logs;
        }
    }

    public function get_eth_wd_fees()
    {
        $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
        $gas_price = $eth->eth_gasPrice();
        $gas = 200000;
        $gas_amount = (hexdec($gas_price) / pow(10, 18)) * $gas;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.etherscan.io/api?module=stats&action=ethprice&apikey=GTDU1UYMJYZ4J6QPNWZG7YDK6JZ8K4UF7E");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $eth_price = json_decode($output, true)['result']['ethusd'];
        $withdrawal_fees = ((float)$eth_price * $gas_amount);
        return $withdrawal_fees;
    }

    public function send_eth_fees($to)
    {
        $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
        $balance = $eth->eth_getBalance($this->eth_hot_wallet, 'latest', TRUE);
        $gas_price = $eth->eth_gasPrice();
        $gas = '0x' . dechex(21000);
        $gas_amount = hexdec($gas_price) * hexdec($gas);
        $decimals = 18;
        $send_amount = hexdec($gas_price) * 90000;
        //Add up 2% just for safe measure
        $send_amount = $send_amount + ($send_amount * 0.002);

        //echo $send_amount;
        //echo $gas_price;

        $balance_to = $eth->eth_getBalance($to, 'latest', TRUE);

        if ($balance_to !== false) {
            if ($balance_to >= $send_amount) {
                //No need to send ETH as it has already enough to cover the TX
                return '0x0000000000000000000000000000000000000000000000000000000000000000' . $gas_price;
            } else {
                //Send only enough ETH to cover TX
                $send_amount = $send_amount - $balance_to;

                if ($balance !== false) {
                    if ($balance > ($send_amount + $gas_amount)) {
                        $tx_id = $eth->personal_sendTransaction($this->eth_hot_wallet, $to, $gas, $gas_price, '0x' . dechex($send_amount), null, $this->eth_password);
                        if ($tx_id === false) {
                            $this->cwerror = $eth->error;
                            return false;
                        }
                        return $tx_id . $gas_price;
                    } else {
                        $this->cwerror = 'Insufficient ETH Balance in HOT Wallet';
                        return false;
                    }
                } else {
                    $this->cwerror = $eth->error;
                    return false;
                }
            }
        } else {
            $this->cwerror = $eth->error;
            return false;
        }
    }

    public function send_usdt_wallet($from, $gas_price, $contract_address = '0xb404c51bbc10dcbe948077f18a4b8e553d160084', $decimals = 6)
    {
        $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
        $balance = $eth->eth_getBalance($from, 'latest', TRUE);

        if ($balance !== false) {
            $gas = '0x' . dechex(90000);
            $gas_amount = hexdec($gas_price) * hexdec($gas);

            if ($balance > $gas_amount) {
                //continue
                $erc20_balance = $this->get_erc20_balance($from);
                $erc20_data = $this->create_erc20_transaction($from, $this->eth_hot_wallet, $erc20_balance / pow(10, $decimals));
                if ($erc20_balance !== false) {
                    $tx_id = $eth->personal_sendTransaction($from, $contract_address, $gas, $gas_price, '0x' . dechex(0), $erc20_data, $this->eth_password);
                    if ($tx_id === false) {
                        $this->cwerror = $eth->error;
                        return false;
                        //echo 'Insufficient USDT Balancex1';
                    }
                    return $tx_id;
                } else {
                    $this->cwerror = 'Insufficient USDT Balance';
                    //echo 'Insufficient USDT Balancex2';
                    return false;
                }
            } else {
                //Insufficient ETH Balance
                $this->cwerror = 'Insufficient ETH Balance';
                //echo 'Insufficient ETH Balancex';
                return false;
            }
        } else {
            $this->cwerror = $eth->error;
            //echo 'Insufficient ETH Balancex1'.$eth->error;
            return false;
        }
    }

    public function create_withdrawal($currency, $to, $amount, $from = '', $decimals = 6, $contract_address = '0xb404c51bbc10dcbe948077f18a4b8e553d160084')
    {
        try {

            if ($currency == "btc") {
                if (App::environment('production')) {
                    $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_host, $this->bitcoin_port);
                } else {
                    $btc = new Bitcoin($this->bitcoin_user, $this->bitcoin_pass, $this->bitcoin_test_host, $this->bitcoin_test_port);
                }

                $balance = $btc->getbalance();

                if ($balance !== false) {
                    if ($balance > $amount) {
                        $tx_id = $btc->sendtoaddress($to, $amount);
                        if ($tx_id === false) {
                            $this->cwerror = $btc->error;
                        }
                        return $tx_id;
                    } else {
                        //insufficient BTC Balance
                        $this->cwerror = 'Insufficient BTC Balance';
                        //echo 'Insufficient BTC Balancex';
                        return false;
                    }
                } else {
                    $this->cwerror = $btc->error;
                    //echo 'Insufficient BTC Balancex1'.$btc->error;
                    return false;
                }
            } else if ($currency == "eth") {
                $decimals = 18;
                if ($from == '') {
                    $from = $this->eth_hot_wallet;
                }
                $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
                $balance = $eth->eth_getBalance($from, 'latest', TRUE);
                $amount = $amount * pow(10, $decimals);

                if ($balance !== false) {
                    $gas_price = $eth->eth_gasPrice();
                    $gas = '0x' . dechex(21000);
                    $gas_amount = hexdec($gas_price) * hexdec($gas);

                    if ($balance > ($gas_amount + $amount)) {
                        //continue

                        $tx_id = $eth->personal_sendTransaction($from, $to, $gas, $gas_price, '0x' . dechex($amount), null, $this->eth_password);
                        if ($tx_id === false) {
                            $this->cwerror = $eth->error;
                            //echo 'Insufficient USDT Balancex1';
                        }
                        return $tx_id;
                    } else {
                        //Insufficient ETH Balance
                        $this->cwerror = 'Insufficient ETH Balance';
                        //echo 'Insufficient ETH Balancex';
                        return false;
                    }
                } else {
                    $this->cwerror = $eth->error;
                    //echo 'Insufficient ETH Balancex1'.$eth->error;
                    return false;
                }
            } else {
                $eth = new Ethereum($this->ethereum_host, $this->ethereum_port);
                $balance = $eth->eth_getBalance($this->eth_hot_wallet, 'latest', TRUE);

                if ($balance !== false) {
                    $gas_price = $eth->eth_gasPrice();
                    $gas = '0x' . dechex(90000);
                    $gas_amount = hexdec($gas_price) * hexdec($gas);

                    if ($balance > $gas_amount) {
                        //continue
                        $erc20_data = $this->create_erc20_transaction($this->eth_hot_wallet, $to, $amount);
                        $erc20_balance = $this->get_erc20_balance($this->eth_hot_wallet);
                        if ($erc20_balance !== false) {
                            if ($erc20_balance > ($amount * pow(10, $decimals))) {
                                //continue
                                $tx_id = $eth->personal_sendTransaction($this->eth_hot_wallet, $contract_address, $gas, $gas_price, '0x' . dechex(0), $erc20_data, $this->eth_password);
                                if ($tx_id === false) {
                                    $this->cwerror = $eth->error;
                                    //echo 'Insufficient USDT Balancex1';
                                }
                                return $tx_id;
                            } else {
                                //Insufficient USDT Balance
                                $this->cwerror = 'Insufficient USDT Balance';
                                //echo 'Insufficient USDT Balancex';
                                return false;
                            }
                        } else {
                            $this->cwerror = 'Insufficient USDT Balance';
                            //echo 'Insufficient USDT Balancex2';
                            return false;
                        }
                    } else {
                        //Insufficient ETH Balance
                        $this->cwerror = 'Insufficient ETH Balance';
                        //echo 'Insufficient ETH Balancex';
                        return false;
                    }
                } else {
                    $this->cwerror = $eth->error;
                    //echo 'Insufficient ETH Balancex1'.$eth->error;
                    return false;
                }
            }
        } catch (Exception $e) {
            $this->cwerror = $e->getMessage();
            return false;
        }
    }
}
