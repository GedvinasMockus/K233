package com.K233.IPS;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContract;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;

import android.Manifest;
import android.app.Activity;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothManager;
import android.bluetooth.le.AdvertiseCallback;
import android.bluetooth.le.AdvertiseData;
import android.bluetooth.le.AdvertiseSettings;
import android.bluetooth.le.BluetoothLeAdvertiser;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.BoringLayout;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.Toast;

import java.nio.ByteBuffer;
import java.util.UUID;

public class ParkActivity extends AppCompatActivity {

    private BluetoothAdapter btAdapt;
    private Button btn;
    private Boolean advertiserSingleton = true;
    private ImageButton btnLogout;
    private SharedPreferences savedData;
    private BluetoothLeAdvertiser advertiser;
    private AdvertiseCallback advCallback;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_park);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        btnLogout = findViewById(R.id.btnLogout);
        btn = findViewById(R.id.btnBarrier);
        savedData = getApplicationContext().getSharedPreferences("UserData", Context.MODE_PRIVATE);

        BluetoothManager btManage = (BluetoothManager) getSystemService(Context.BLUETOOTH_SERVICE);
        if (Build.VERSION.SDK_INT >= 31) {
            btAdapt = btManage.getAdapter();
        } else {
            btAdapt = BluetoothAdapter.getDefaultAdapter();
        }
        try {
            if (!btAdapt.isEnabled()) {
                Intent enableBtIntent = new Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE);
                if (ActivityCompat.checkSelfPermission(this, Manifest.permission.BLUETOOTH_CONNECT) != PackageManager.PERMISSION_GRANTED) {
                    if (Build.VERSION.SDK_INT >= 31) {
                        ActivityCompat.requestPermissions(ParkActivity.this, new String[]{Manifest.permission.BLUETOOTH_CONNECT}, 1);
                    }
                }
                startActivityForResult(enableBtIntent, 3);
            }
        } catch (Exception ignored) {
        }

        btnLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                SharedPreferences.Editor editor = savedData.edit();
                editor.clear();
                editor.apply();
                Intent intent = new Intent(ParkActivity.this, MainActivity.class);
                startActivity(intent);
            }
        });

        btn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                sendBeacon();
            }
        });
    }

    private static byte[] getPayload(String uuid, int major, int minor) {
        byte[] prefixArray = getBytesFromShort((short) 533);
        byte[] uuidArray = getBytesFromUUID(uuid);
        byte[] majorArray = getBytesFromShort((short) major);
        byte[] minorArray = getBytesFromShort((short) minor);
        byte[] powerArray = {(byte) -69};

        byte[] allByteArray = new byte[prefixArray.length + uuidArray.length + majorArray.length + minorArray.length + powerArray.length];

        ByteBuffer buff = ByteBuffer.wrap(allByteArray);
        buff.put(prefixArray);
        buff.put(uuidArray);
        buff.put(majorArray);
        buff.put(minorArray);
        buff.put(powerArray);

        return buff.array();
    }

    private static byte[] getBytesFromUUID(String uuidString) {
        final UUID uuid = UUID.fromString(uuidString);
        ByteBuffer buffer = ByteBuffer.wrap(new byte[16]);
        buffer.putLong(uuid.getMostSignificantBits());
        buffer.putLong(uuid.getLeastSignificantBits());
        return buffer.array();
    }

    private static byte[] getBytesFromShort(short value) {
        ByteBuffer buffer = ByteBuffer.wrap(new byte[2]);
        buffer.putShort(value);
        return buffer.array();
    }

    private void sendBeacon() {
        if (btAdapt.isEnabled()) {
            if (advertiserSingleton){
                advertiserSingleton = false;
                advertiser = btAdapt.getBluetoothLeAdvertiser();
                AdvertiseSettings parameters = new AdvertiseSettings.Builder().setAdvertiseMode(AdvertiseSettings.ADVERTISE_MODE_LOW_LATENCY).setConnectable(false).setTxPowerLevel(AdvertiseSettings.ADVERTISE_TX_POWER_HIGH)
                        .build();

                byte[] payload = getPayload(savedData.getString("UUID", "ffffffff-ffff-ffff-ffff-ffffffffffff"), 10, 11);
                AdvertiseData data = new AdvertiseData.Builder().addManufacturerData(0x004C, payload).build();
                AdvertiseCallback callback = new AdvertiseCallback() {
                    @Override
                    public void onStartSuccess(AdvertiseSettings settingsInEffect) {
                        super.onStartSuccess(settingsInEffect);
                        Log.v("TAG", "onStartSuccess");
                    }

                    @Override
                    public void onStartFailure(int errorCode) {
                        Log.e("TAG", "Advertising onStartFailure: " + errorCode);
                        advertiserSingleton = true;
                        super.onStartFailure(errorCode);
                    }
                };
                advCallback = callback;
                if (ActivityCompat.checkSelfPermission(ParkActivity.this, Manifest.permission.BLUETOOTH_ADVERTISE) != PackageManager.PERMISSION_GRANTED) {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                        ActivityCompat.requestPermissions(ParkActivity.this, new String[]{Manifest.permission.BLUETOOTH_ADVERTISE}, 1);
                    } else {
                        ActivityCompat.requestPermissions(ParkActivity.this, new String[]{Manifest.permission.BLUETOOTH_ADMIN}, 1);
                    }
                }
                advertiser.startAdvertising(parameters, data, advCallback);
                Handler beaconStopHandler = new Handler(Looper.getMainLooper());
                beaconStopHandler.postDelayed(new Runnable() {
                    @Override
                    public void run() {
                        stopBeaconSignal();
                    }
                }, 15000);
            }
        } else {
            Log.e("alert", "alert");
            AlertDialog.Builder alertBuilder = new AlertDialog.Builder(this);
            alertBuilder.setTitle("\"Bluetooth\" neįjungtas").setMessage("Norint atidaryti barjerą, reikia įjungti \"Bluetooth\" telefone!").setPositiveButton("Supratau", new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialogInterface, int i) {
                    dialogInterface.cancel();
                }
            }).show();
        }

    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == 3) { // the request code passed to startActivityForResult()
            if (resultCode == RESULT_OK) { // Bluetooth was successfully enabled
                Toast.makeText(this, "Bluetooth enabled", Toast.LENGTH_SHORT).show();
//                btn.setEnabled(true);
            } else { // the user declined to enable Bluetooth
                Toast.makeText(this, "Bluetooth not enabled", Toast.LENGTH_SHORT).show();
//                btn.setEnabled(false);
                // Perform your desired action here if the user declines to enable Bluetooth
            }
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);

        if (requestCode == 1) {
            // Checking whether user granted the permission or not.
            if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                // Showing the toast message
                Toast.makeText(ParkActivity.this, "BT Permission Granted", Toast.LENGTH_SHORT).show();
            }
        } else if (requestCode == 2) {
            if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                // Showing the toast message
                Toast.makeText(ParkActivity.this, "BT Permission Granted", Toast.LENGTH_SHORT).show();
            }
        } else if (requestCode == 3) {
            if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                // Showing the toast message
                Toast.makeText(ParkActivity.this, "BT stop permission granted", Toast.LENGTH_SHORT).show();
            }
        }
    }

    @Override
    protected void onStop() {
        if (!advertiserSingleton) {
            this.stopBeaconSignal();
        }
        super.onStop();
    }

    private void stopBeaconSignal() {
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.BLUETOOTH_ADVERTISE) != PackageManager.PERMISSION_GRANTED) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                ActivityCompat.requestPermissions(ParkActivity.this, new String[]{Manifest.permission.BLUETOOTH_ADVERTISE}, 3);
            } else {
                ActivityCompat.requestPermissions(ParkActivity.this, new String[]{Manifest.permission.BLUETOOTH_ADMIN}, 3);
            }
        }
        advertiserSingleton = true;
        advertiser.stopAdvertising(advCallback);
    }
}
