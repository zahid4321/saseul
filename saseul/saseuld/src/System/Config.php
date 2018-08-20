<?php
namespace src\System;

class Config {
    public static $version = '0.1';
    public static $key_algorithm = 'Ed25519';
    public static $address_prefix_0 = '0x00';
    public static $address_prefix_1 = '0x6f';

    public static $database = 'MongoDB';

    public static $database_mongodb_host = 'localhost:27017';
    public static $database_mongodb_name_consensus = 'pv_saseul_consensus';
    public static $database_mongodb_name_precommit = 'pv_saseul_precommit';
    public static $database_mongodb_name_committed = 'pv_saseul_committed';
    public static $database_mongodb_name_tracker = 'pv_saseul_tracker';

    public static $node_private_key = 'b53388d3b96d89a9eda8d3420dc9079cf52a61875e48e434000573b901f0a5eb';
    public static $node_public_key = 'c2d4884802bbe62d5a23d4876e3222b356bebb71af128a96dcb357660b77c020';
    public static $node_address = '0x6ff890886f26bb7dc057b516d01d5f4e4970b9489d507c';

    public static $transaction_type_genesis = 'Genesis';
    public static $transaction_type_send_coin = 'SendCoin';
    public static $transaction_type_deposit = 'Deposit';
    public static $transaction_type_withdraw = 'WithDraw';
    public static $transaction_type_uptosupervisor = 'UpToSupervisor';
    public static $transaction_type_uptovalidator = 'UpToValidator';
    public static $transaction_type_get_balance = 'GetBalance';

    public static $v_transaction_type_chunk_broadcast = 'ChunkBroadcast';
    public static $v_transaction_type_addtracker = 'AddTracker';

    public static $microinterval_validtransaction = 3 * 1000000;
    public static $microinterval_chunk = 3 * 1000000;
    public static $fee_rate = 0.00015;
    public static $fee_rate_min = 0.0001;

    public static $testable = true;

    public static $directory_broadcast_chunks = '/var/saseul/api/web/Files/BroadcastChunks/';
    public static $directory_api_chunks = '/Files/BroadcastChunks/';
    public static $server_host = '';

    public static $broadcast_request_url = '/validator/broadcastrequest';
    public static $broadcast_timeout = 2;

    public static $default_heartbeat = 3 * 1000000;
}
