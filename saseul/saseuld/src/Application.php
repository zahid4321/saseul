<?php
namespace src;

use src\Func\Consensus;
use src\Store\Chunk;
use src\System\Config;
use src\Util\Logger;
use src\Validator\Broadcaster;

class Application {

    private $chunk;
    private $consensus;
    private $broadcaster;

    private $m_consensus_status;

    private $m_broadcast_chunks;
    private $m_broadcast_chunk_urls;

    private $m_collect_chunk_urls;

    private $m_heartbeat;
    private $m_precommit_timestamp;
    private $m_next_timestamp;

    function __construct()
    {
        $this->chunk = new Chunk();
        $this->consensus = new Consensus();
        $this->broadcaster = new Broadcaster();

        $this->m_consensus_status = 'early';
//        $this->m_consensus_status = 'later';

        $this->m_heartbeat = Config::$default_heartbeat;
        $this->m_precommit_timestamp = Logger::Microtime();
        $this->m_next_timestamp = Logger::Microtime() + $this->m_heartbeat;
    }

    function Main() {
        # Routine
        $this->BroadcastChunk();
        $this->CollectChunk();

        # If need sleep
//        usleep($this->m_heartbeat);
        $this->PreCommit();
        $this->Consensus();
    }

    function BroadcastChunk() {
        \System_Daemon::info(date('Y-m-d H:i:s') . " - BroadcastChunk");
        $this->m_broadcast_chunks = [];
        $this->m_broadcast_chunks = $this->chunk->GetBroadcastChunks();
        $this->m_broadcast_chunk_urls = $this->chunk->WriteBroadcastChunks($this->m_broadcast_chunks);

        if (count($this->m_broadcast_chunk_urls) == 0)
            return;

        $this->broadcaster->BroadcastChunk($this->m_broadcast_chunk_urls);
    }

    function CollectChunk() {
        \System_Daemon::info(date('Y-m-d H:i:s') . " - CollectChunk");

        $this->m_collect_chunk_urls = [];
        $this->m_collect_chunk_urls = $this->chunk->GetChunkUrl();

        if (count($this->m_collect_chunk_urls) == 0)
            return;

        $this->chunk->WriteCollectChunk($this->m_collect_chunk_urls);
    }

    function Consensus() {
        if ($this->m_next_timestamp > Logger::Microtime()) {
            return;
        }

        switch ($this->m_consensus_status) {
            case 'early':
                $this->EarlyConsensus();
                break;
            case 'later':
                $this->LaterConsensus();
                break;
            default:
                break;
        }
    }

    function PreCommit() {
        \System_Daemon::info(date('Y-m-d H:i:s') . " - PreCommit");
        # after heartbeat
        $this->m_precommit_timestamp = Logger::Microtime() - $this->m_heartbeat;
        $this->consensus->PreCommit($this->m_precommit_timestamp);
    }

    function ChangeStatus($status) {
        $this->m_consensus_status = $status;
    }

    function EarlyConsensus() {
        \System_Daemon::info(date('Y-m-d H:i:s') . " - EarlyConsensus");

        $blockhash = $this->consensus->GetBlockhash($this->m_next_timestamp);
        $this->ChangeStatus('later');
    }

    function LaterConsensus() {
        \System_Daemon::info(date('Y-m-d H:i:s') . " - LaterConsensus");

        $blockhash = $this->consensus->GetBlockhash($this->m_next_timestamp);
        $this->consensus->ConsensusCommit($this->m_next_timestamp);

        $this->ChangeStatus('early');
        $this->m_next_timestamp = Logger::Microtime() + $this->m_heartbeat;
    }
}