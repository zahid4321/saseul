<?php
namespace System;
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

    public static $genesis_key = 'pv-saseul';
    public static $genesis_coin_value = 100 * 10000 * 10000 * 10000 * 10000;
    public static $genesis_deposit_value = 50 * 10000 * 10000 * 10000 * 10000;

    public static $genesis_private_key = 'b53388d3b96d89a9eda8d3420dc9079cf52a61875e48e434000573b901f0a5eb';
    public static $genesis_public_key = 'c2d4884802bbe62d5a23d4876e3222b356bebb71af128a96dcb357660b77c020';
    public static $genesis_address = '0x6ff890886f26bb7dc057b516d01d5f4e4970b9489d507c';
    public static $genesis_request_cache_interval = 15;

    public static $node_private_key = 'b53388d3b96d89a9eda8d3420dc9079cf52a61875e48e434000573b901f0a5eb';
    public static $node_public_key = 'c2d4884802bbe62d5a23d4876e3222b356bebb71af128a96dcb357660b77c020';
    public static $node_address = '0x6ff890886f26bb7dc057b516d01d5f4e4970b9489d507c';

    public static $testnode_private_key = 'd8fb00f132df37b564902bd79a70ddbf8dc4c44b48e909fb000573d84912a802';
    public static $testnode_public_key = '5dd6f6c289b87c51af848c844e2afae5a692b854d84876b9225fea7f0a396ed6';
    public static $testnode_address = '0x6f70b91b9e41d1640b942df2451aef44658c7c08391c68';

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

    public static $testable = false;
}
