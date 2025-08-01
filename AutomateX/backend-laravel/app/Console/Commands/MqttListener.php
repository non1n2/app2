<?php

namespace App\Console\Commands;

use App\Models\Qr;
use App\Events\QrEvent; // Your existing event
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

class MqttListener extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listens for MQTT messages from IoT QR Scanners';

    public function handle()
    {
        $this->info("Starting MQTT Listener...");

        $mqtt = MQTT::connection();
        $topic = 'esp-cam/scan/data'; // The topic the ESP will publish to

        $mqtt->subscribe($topic, function (string $topic, string $message) {
            $this->info("Received QR data on topic [{$topic}]: {$message}");
            Log::info("MQTT Message Received: " . $message);

            $data = json_decode($message, true);

            // --- DATA VALIDATION ---
            if (!$data || !isset($data['qrData'])) {
                Log::warning('Received malformed MQTT message.');
                return; // Ignore malformed message
            }

            $qrCodeValue = $data['qrData'];

            // --- REUSE YOUR EXISTING LOGIC ---
            // We'll mimic what your QrController does.

            // Find if the Qr already exists
            $Qr = Qr::where('value', $qrCodeValue)->first();
            
            // For a private channel, we need a user. For this IoT context,
            // we'll hardcode a "system" user or a specific user ID.
            // IMPORTANT: Change '1' to the ID of the user you want to broadcast to.
            $targetUserId = 1; 

            if ($Qr) {
                // IT'S AN UPDATE
                $this->info("Qr [{$qrCodeValue}] exists. Processing as UPDATE.");
                $Qr->touch(); // Just update the updated_at timestamp
                $isUpdate = true;
                event(new QrEvent($Qr, $targetUserId, $isUpdate));
            } else {
                // IT'S A NEW ENTRY
                $this->info("Qr [{$qrCodeValue}] is new. Processing as CREATE.");
                try {
                    $newQr = Qr::create([
                        'value' => $qrCodeValue,
                        // Add any other required fields with default values
                        // 'part_id' => 1, // Example
                    ]);
                    $isUpdate = false;
                    event(new QrEvent($newQr, $targetUserId, $isUpdate));
                } catch (\Exception $e) {
                    Log::error("Failed to create Qr from MQTT: " . $e->getMessage());
                }
            }

        }, 0); // QoS level 0 is fine for this

        $mqtt->loop(true);
    }
}