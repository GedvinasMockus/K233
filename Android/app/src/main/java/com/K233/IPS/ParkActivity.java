package com.K233.IPS;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContract;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.gridlayout.widget.GridLayout;

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
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.Rect;
import android.net.ConnectivityManager;
import android.net.Network;
import android.net.NetworkCapabilities;
import android.net.NetworkInfo;
import android.net.NetworkRequest;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.BoringLayout;
import android.util.DisplayMetrics;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.ByteBuffer;
import java.util.Random;
import java.util.UUID;

public class ParkActivity extends AppCompatActivity {

    private BluetoothAdapter btAdapt;
    private Button btn;
    private Boolean advertiserSingleton = true;
    private ImageButton btnLogout;
    private ImageView imgLot;
    private TextView txtParkingSpot;
    private SharedPreferences savedData;
    private BluetoothLeAdvertiser advertiser;
    private AdvertiseCallback advCallback;
    boolean isConnected = true;
    private String POSTresponse = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_park);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        txtParkingSpot = findViewById(R.id.txtParkingSpot);
        txtParkingSpot.setVisibility(View.INVISIBLE);
        imgLot = findViewById(R.id.imgLot);
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
            new Thread(new Runnable() {
                @Override
                public void run() {
                    checkForConnection();
                }
            }).start();

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
            AlertDialog.Builder alertBuilder = new AlertDialog.Builder(this);
            alertBuilder.setTitle("\"Bluetooth\" neįjungtas").setMessage("Norint atidaryti barjerą, reikia įjungti \"Bluetooth\" telefone!").setPositiveButton("Supratau", new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialogInterface, int i) {
                    dialogInterface.cancel();
                }
            }).show();
        }

    }

    private void checkForConnection() {
        ConnectivityManager connectivityManager = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo activeNetworkInfo = connectivityManager.getActiveNetworkInfo();
        isConnected = activeNetworkInfo != null && activeNetworkInfo.isConnectedOrConnecting();
        if (!isConnected) {
            runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    txtParkingSpot.setVisibility(View.VISIBLE);
                    Toast.makeText(getApplicationContext(), "No connection", Toast.LENGTH_SHORT).show();
                }
            });
        } else {
            getParkingSpot();
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

    private void getParkingSpot(){
        try {
            URL url = new URL("http://78.62.39.220:3000/openBarrier");
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Content-Type", "application/json");
            connection.setDoOutput(true);

            JSONObject json = new JSONObject();
            json.put("uuid", savedData.getString("UUID","ffffffff-ffff-ffff-ffff-ffffffffffff" ));
            json.put("email", savedData.getString("email", "empty@empty.com"));

            OutputStreamWriter writer = new OutputStreamWriter(connection.getOutputStream());
            writer.write(json.toString());
            writer.flush();

            int statusCode = connection.getResponseCode();
            if (statusCode == HttpURLConnection.HTTP_OK) {
                StringBuffer response = new StringBuffer();
                InputStream inputStream = connection.getInputStream();
                BufferedReader reader = new BufferedReader(new InputStreamReader(inputStream));
                String line;
                while ((line = reader.readLine()) != null) {
                    response.append(line);
                }
                reader.close();
                inputStream.close();
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        POSTresponse = String.valueOf(response);
                        showParkingSpot();
                    }
                });

            } else {
                // Handle the error
            }
        } catch (Exception e) {
            // Handle the exception
            e.printStackTrace();
        }
    }
    private void showParkingSpot() {
        txtParkingSpot.setVisibility(View.VISIBLE);
        if (POSTresponse.equals("\"Vartotojo informacija nerasta!\"") || POSTresponse.equals("\"Rezervacija nerasta\"")) {
            txtParkingSpot.setText(POSTresponse);
        } else {
            try {
                String[] coords = new String[4];
                JSONArray jsonArr = new JSONArray(POSTresponse);
                JSONObject json = jsonArr.getJSONObject(0);
                String parkSpace = json.getString("space_number");
                String parkLot = json.getString("parking_name");
                String pic = json.getString("photo_path");
                JSONArray coordinates = json.getJSONArray("coordinates");
                for (int i = 0; i < 4; i++) {
                    String coordinate = coordinates.getString(i);
                    coords[i] = coordinate;
                }
                String txtToShow = parkSpace + " vieta aikštelėje \"" + parkLot + "\"";
                txtParkingSpot.setText(txtToShow);
                Thread lotDisplay = new Thread(new Runnable() {
                    @Override
                    public void run() {
                        try {
                            URL url = new URL(pic);
                            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                            connection.setDoInput(true);
                            connection.connect();
                            InputStream input = connection.getInputStream();
                            Bitmap bitmap = BitmapFactory.decodeStream(input);

                            Bitmap newBitmap = Bitmap.createBitmap(bitmap.getWidth(), bitmap.getHeight(), Bitmap.Config.ARGB_8888);
                            Canvas canvas = new Canvas(newBitmap);
                            canvas.drawBitmap(bitmap, 0, 0, null);

                            Paint paint = new Paint();
                            paint.setColor(Color.BLACK);
                            paint.setStyle(Paint.Style.STROKE);
                            paint.setStrokeWidth(1);
                            Path path = new Path();
                            path.moveTo(Float.parseFloat(coords[0].split(",")[0]), Float.parseFloat(coords[0].split(",")[1]));
                            for (int i = 1; i < 4; i++) {
                                path.lineTo(Float.parseFloat(coords[i].split(",")[0]), Float.parseFloat(coords[i].split(",")[1]));
                            }
                            path.close();

                            paint.setStyle(Paint.Style.FILL);
                            paint.setColor(Color.parseColor("#8000FF00"));
                            canvas.drawPath(path, paint);

                            paint.setColor(Color.BLACK);
                            paint.setStyle(Paint.Style.FILL);
                            float maxTextWidth = Float.parseFloat(coords[2].split(",")[0]) - Float.parseFloat(coords[0].split(",")[0]);
                            float maxTextHeight = Float.parseFloat(coords[2].split(",")[1]) - Float.parseFloat(coords[0].split(",")[1]);
                            float textHeight = 0;
                            float textSize = 1;
                            do {
                                paint.setTextSize(textSize++);
                                textHeight = paint.getFontMetrics().bottom - paint.getFontMetrics().top;
                            } while (paint.measureText(parkSpace) < maxTextWidth && textHeight < maxTextHeight);

                            if(textSize<5){
                                textSize=25;
                                paint.setTextSize(textSize);
                                textHeight = paint.getFontMetrics().bottom - paint.getFontMetrics().top;
                            }
                            Log.d("MyApp", "textSize: " + textSize);
                            float textWidth = paint.measureText(parkSpace);
                            float centerX = (Float.parseFloat(coords[0].split(",")[0]) + Float.parseFloat(coords[2].split(",")[0])) / 2;
                            float centerY = (Float.parseFloat(coords[0].split(",")[1]) + Float.parseFloat(coords[2].split(",")[1])) / 2;
                            canvas.drawText(parkSpace, centerX - (textWidth / 2), centerY + (textHeight / 4), paint);




                            DisplayMetrics displayMetrics = new DisplayMetrics();
                            getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
                            int screenWidth = displayMetrics.widthPixels;
                            float scaleFactor = (float) screenWidth / (float) newBitmap.getWidth();
                            int newWidth = (int) (newBitmap.getWidth() * scaleFactor);
                            int newHeight = (int) (newBitmap.getHeight() * scaleFactor);
                            newBitmap = Bitmap.createScaledBitmap(newBitmap, newWidth, newHeight, true);
                            Bitmap finalBitmap = newBitmap;

                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    imgLot.setImageBitmap(finalBitmap);
                                    imgLot.setScaleType(ImageView.ScaleType.MATRIX);
                                    imgLot.setVisibility(View.VISIBLE);
                                }
                            });

                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    }
                });
                lotDisplay.start();
            }
            catch (JSONException e) {
                Log.e("JSON klaida", "Pateikti nekorektiški rezervacijos duomenys");
                e.printStackTrace();
            }
        }
    }


}
