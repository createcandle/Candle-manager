/*
 *
 * DESCRIPTION
 * 
 * This node can measure the moisture of 6 different plants. It uses the cheap 'capacitive analog 
 * moisture sensor' that you can get for about 3 dollars an Aliexpress or eBay. For example:
 * https://www.aliexpress.com/item/Analog-Capacitive-Soil-Moisture-Sensor-V1-2-Corrosion-Resistant-Z09-Drop-ship/32858273308.html
 * 
 * Each plant' moisture value can also be responded to individually, either by turning on an LED (wire that to the plan, and you can see which one is thirsty) or, if you want, per-plant automated irrigation by connecting a little solenoid..
 * 
 * Todo: Allow the controller to set the threshold values for each plant individually. Unfortunately, Domoticz doesn't support this yet :-(
 * 
 * SETTINGS */

#define NUMBER_OF_SENSORS 6                         // How many moisture sensors have you connected?

#define SLEEPTIME 100                               // How many seconds should pass between checking on the plants and sending the data? Don't make this less than 15 or more than 255.

#define HAS_SCREEN                                  // Have you connected a screen?

/* END OF SETTINGS
  *  
  *  
  *  
  */

// Enable MySensors debug prints to serial monitor
//#define MY_DEBUG 

// Enable and select radio type attached
#define MY_RADIO_NRF24
//#define MY_RADIO_NRF5_ESB
//#define MY_RADIO_RFM69
//#define MY_RADIO_RFM95

// Set LOW transmit power level as default, if you have an amplified NRF-module and power your radio separately with a good regulator you can turn up PA level. Choose one:
//#define MY_RF24_PA_LEVEL RF24_PA_MIN
#define MY_RF24_PA_LEVEL RF24_PA_LOW
//#define MY_RF24_PA_LEVEL RF24_PA_HIGH
//#define MY_RF24_PA_LEVEL RF24_PA_MAX

// Easy to use security, yay!
//#define MY_SIGNING_SOFT_RANDOMSEED_PIN A7         // setting a pin to pickup noise makes encryption more secure.

// Advanced features
#define MY_TRANSPORT_WAIT_READY_MS 10000            // try connecting for 10 seconds. Otherwise just continue.
//#define MY_RF24_CHANNEL 100                       // in EU the default channel 76 overlaps with wifi.
//#define MY_RF24_DATARATE RF24_1MBPS               // slower datarate makes the network more stable?
//#define MY_NODE_ID 13                             // giving a node a manual ID can in rare cases fix connection issues.
//#define MY_PARENT_NODE_ID 0                       // fixating the ID of the gatewaynode can in rare cases fix connection issues.
//#define MY_PARENT_NODE_IS_STATIC                  // Used together with setting the parent node ID. Daking the controller ID static can in rare cases fix connection issues.
#define MY_SPLASH_SCREEN_DISABLED                   // saves a little memory.
//#define MY_DISABLE_RAM_ROUTING_TABLE_FEATURE      // saves a little memory.



//
// FURTHER SETTINGS
//

#define IRRIGATION_RELAYS 6                         // How many irrigation relays are connected?
#define SLEEPTIME 100                               // In seconds, how often should a measurement be made and sent to the server? The maximum delay between measurements is once every 254 seconds, but if you change "byte" to "int" further down in the code you could create more time between each loop.
#define LOOPDURATION 5000                           // The main loop runs every x milliseconds. This main loop starts the modem, and from then on periodically requests the password.

#define RF_DELAY 100                                // Milliseconds betweeen radio signals during the presentation phase.

// Do you want this node to also be a repeater?
#define MY_REPEATER_FEATURE                         // Just add or remove the two slashes at the beginning of this line to select if you want this sensor to act as a repeater for other sensors. If this node is on battery power, you probably shouldn't enable this. Otherwise it's smart to make every node also a repeater.


//
// Do not change below this line
// 

#include <MySensors.h>



#ifdef HAS_SCREEN
#include <SoftwareSerial.h>
SoftwareSerial mySerial(7,6);                       // RX, TX
#endif


static const uint8_t analog_pins[] = {A0,A1,A2,A3,A4,A5};
byte moistureLevels[6] = {1, 2, 3, 4, 5, 6};
byte moistureThresholds[6] = {35, 35, 35, 35, 35, 35}; // for each plant we can have a unique moisture level to compare against.
MyMessage thresholdMsg(0, V_PERCENTAGE);            // used to create a dimmer on the controller that controls the mosture threshold;
MyMessage msg(0, V_LEVEL);                          // used to send moisture level data to the gateway

//const char xxxx = "hello";
//const char ids[7] = "112233445566";


void before()
{

  //for (byte i = 3; i < NUMBER_OF_SENSORS + 3; i++){ // Set the LED (or irrigation vales) to their initial position. Because Mysensors uses pin 2, we use pin 3 till 8 as output.
  //  pinMode(i, OUTPUT);
  //  digitalWrite(i, LOW);
  //}
  
}


void presentation()
{
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo(F("Plant Health"), F("1.3"));  wait(RF_DELAY);

  // Present the sensors
  //for (byte i=0; i<NUMBER_OF_SENSORS*2 ; i=i+2) {
    present(0, S_MOISTURE, "1");  wait(RF_DELAY);       // present all the sensors
    present(1, S_DIMMER, "1");  wait(RF_DELAY);       // present the dimmers to set the level with.
    present(2, S_MOISTURE, "2");  wait(RF_DELAY);       // present all the sensors
    present(3, S_DIMMER, "2");  wait(RF_DELAY);       // present the dimmers to set the level with.
    present(4, S_MOISTURE, "3");  wait(RF_DELAY);       // present all the sensors
    present(5, S_DIMMER, "3");  wait(RF_DELAY);       // present the dimmers to set the level with.
    present(6, S_MOISTURE, "4");  wait(RF_DELAY);       // present all the sensors
    present(7, S_DIMMER, "4");  wait(RF_DELAY);       // present the dimmers to set the level with.
    present(8, S_MOISTURE, "5");  wait(RF_DELAY);       // present all the sensors
    present(9, S_DIMMER, "5");  wait(RF_DELAY);       // present the dimmers to set the level with.
    present(10, S_MOISTURE, "6");  wait(RF_DELAY);       // present all the sensors
    present(11, S_DIMMER, "6");  wait(RF_DELAY);       // present the dimmers to set the level with.
  //}


}

void setup()
{
  Serial.begin(115200);                             // Start serial output of data.
  while (!Serial) {}                                // Wait for serial connection to be initiated
  Serial.println(F("Hello world"));

  //Serial.print("ids-"); Serial.println(ids[1]);

  // Setup pins for input
  //for (byte i = 15; i < 21; i++) {
  //}

  // Setup pins for input
  for (int i = 0; i < NUMBER_OF_SENSORS; i++) { //or i <= 5
    pinMode(analog_pins[i], INPUT); // experimental new: added the pullup.
     wait(1);
  }


#ifdef HAS_SCREEN
  mySerial.begin(115200);
  //wait(1500);
  //mySerial.print("RESET;");
  wait(1500);
  mySerial.print("CLR(0);");
  mySerial.print("BOXF(0,0,128,18,15);");
  mySerial.print("DCV16(5,1,Plant health, 10);");
  if(isTransportReady()){
    mySerial.print("DCV16(115,1,w,0);");
  }
  for (byte i=0; i<NUMBER_OF_SENSORS ; i++) {
    byte verticalPosition = i*18+28;
    mySerial.print("BOXF(3," + String(verticalPosition) + ",124," + String(verticalPosition + 16) + ",15);");
    mySerial.print("DCV16(5," + String(verticalPosition) + ",A" + i + ",0);");
  }
  mySerial.println();
  wait(500);
#endif

  // load the threshold level from the built-in EEPROM memory.
  for (byte i=0; i<NUMBER_OF_SENSORS ; i++) {
    moistureThresholds[i] = loadState(i);
    if(moistureThresholds[i] > 99){moistureThresholds[i] = 35;}
    Serial.print(F("Loaded: ")); Serial.println(moistureThresholds[i]);
  }
  
  Serial.println(F("Warming up the sensors (15 seconds).")); // to avoid weird measurements
  wait(15000);
  request(1, V_PERCENTAGE ); wait(RF_DELAY); // to-do: only present the actually connected number of sensors.
  request(3, V_PERCENTAGE ); wait(RF_DELAY);
  request(5, V_PERCENTAGE ); wait(RF_DELAY);
  request(7, V_PERCENTAGE ); wait(RF_DELAY);
  request(9, V_PERCENTAGE ); wait(RF_DELAY);
  request(11, V_PERCENTAGE ); wait(RF_DELAY);

  wdt_enable(WDTO_8S);                              // Starts the watchdog timer. If it is not reset once every 2 seconds, then the entire device will automatically restart.                                
}


void loop()
{

  //
  // MAIN LOOP
  // Runs every few seconds. By counting how often this loop has run (and resetting that counter back to zero after 250 loops), it becomes possible to schedule all kinds of things without using a lot of memory.
  // Maximum time that can be scheduled is 4s * 250 loops = 1000 seconds. So the maximum time between sending data can be 16 minutes.
  //

  static byte loopCounter = 0;                      // Counts the loops until the SLEEPTIME value has been reached. Then new data is sent to the controller.
  static boolean loopDone = false;                  // used to make sure the 'once every millisecond' things only run once every millisecond (or 2.. sometimes the millis() function skips a millisecond.);

  // Avoid the loop running at the speed of the processor (multiple times per millisecond). This entire construction saves memory by not using a long to store the last time the loop ran.
  if( (millis() % LOOPDURATION) > LOOPDURATION - 4 && loopDone == true ) {
    loopDone = false;  
  }

  // Main loop to time actions.
  if( (millis() % LOOPDURATION) < 4 && loopDone == false ) { // the 4 is just a precaution: sometimes the milli() function skips a millisecond. This ensure the loop code still runs in that rare case.
    loopDone = true;
    //loopCounter++;
    Serial.print(F("__loop_"));Serial.println(loopCounter);

    wdt_reset(); // Reset the watchdog timer

    byte selectedSensor = loopCounter % NUMBER_OF_SENSORS;
    Serial.print(F("_modulo:_")); Serial.println(selectedSensor);
    //for (byte i=0; i<NUMBER_OF_SENSORS; i++){         // loop over all the sensors.



      int16_t moistureLevel = analogRead(analog_pins[selectedSensor]);
      Serial.print(F(" moisture level (pre): "));
      Serial.println(moistureLevel);
      if(moistureLevel > 700){moistureLevel = 700;}
      moistureLevel = map(moistureLevel,0,700,0,99); // The maximum voltage output of the capacitive sensor is 3V, so since we're measuring 0-5v about 614 is the highest value we'll ever get.
      Serial.print(selectedSensor);
      Serial.print(F(" moisture level %: "));
      Serial.println(moistureLevel);

      //if(moistureLevels[selectedSensor] != moistureLevel){

      //byte dimmerID = selectedSensor + 1; //(selectedSensor*2)+1;
      //Serial.print(F("Requesting dimmer ")); Serial.println(dimmerID);
      // request(dimmerID, V_DIMMER ); // from now on we let the server push the value.
      moistureLevels[selectedSensor] = moistureLevel;   
         
#ifdef HAS_SCREEN
      drawItem(selectedSensor);
 
#endif



      //}
      //if(digitalRead(shiftedDigitalPin) == HIGH){  // outputs the LED/irrigation status via serial. This code can be removed.
      //  Serial.print(F("- currently watering until "));
      //  Serial.println(moistureThresholds[selectedSensor] + 10);
      //}


      // TO-DO actuator
      // byte shiftedDigitalPin = selectedSensor + 3;
      
      if( moistureLevel < moistureThresholds[selectedSensor] ){   // if the plant doesn' have enough water, turn on the LED/water.
        Serial.print(F("- moisture level is below ")); Serial.println(moistureThresholds[selectedSensor]);
        //digitalWrite(shiftedDigitalPin, HIGH);
      }else if (moistureLevel >= moistureThresholds[selectedSensor] + 10){  // turn of the water/led if the plant is wet enough.
        //digitalWrite(shiftedDigitalPin, LOW);
      }

      if(loopCounter == 0){                          
        // Whole dealing with the first sensor we also do a check if the server is responding ok. It it doesn't respond, remove the connection icon.
        if(send(msg.setSensor(selectedSensor*2).set(moistureLevel),1)){ // ask for a receipt
          Serial.println(F("Connection is ok"));
#ifdef HAS_DISPLAY
          // add W icon
          mySerial.print(F("DCV16(115,1,w,0);"));
#endif
        }else {
          Serial.println(F("Connection lost"));
#ifdef HAS_DISPLAY
          // remove W icon
          mySerial.print(F("DCV16(115,1, ,0);"));
#endif        
        }
        
      } else if(loopCounter < NUMBER_OF_SENSORS){       // During the first few loops the script will send updated data.
        if(loopCounter == selectedSensor){                           // It sends sensor 0 at second 0. Sensor 1 at second 1, etc. This keeps the radio happy.
          Serial.println(F("- sending data."));
          send(msg.setSensor(selectedSensor*2).set(moistureLevel));  // 0, 2, 4 etc
        }
      }
    
    loopCounter++;
    if(loopCounter >= SLEEPTIME){                       // If enough time has passed, the counter is reset, and new data is sent.
      loopCounter = 0;
    }
  }
}



void receive(const MyMessage &message)
{
  Serial.print(F("<- message for child #")); Serial.println(message.sensor);
  
  if (message.type == V_PERCENTAGE) {

    //  Retrieve the power or dim level from the incoming request message
    int requestedLevel = atoi( message.data );
    Serial.print(F("Requested level is "));
    Serial.println( requestedLevel );
    
    byte sensorID = (message.sensor - 1) / 2;
    // Adjust incoming level if this is a V_LIGHT variable update [0 == off, 1 == on]
    // requestedLevel *= ( message.type == V_LIGHT ? 100 : 1 );

    //if(message.sensor == ROTATING_PASSWORD2_ID){

    Serial.print(F("Before clipping requested level: "));
    Serial.println( requestedLevel );

    // Clip incoming level to valid range of 0 to 100
    //requestedLevel = requestedLevel > 100 ? 100 : requestedLevel;
    //requestedLevel = requestedLevel < 0   ? 0   : requestedLevel;
    if(requestedLevel > 100){ requestedLevel = 100;}
    if(requestedLevel < 0){ requestedLevel = 0;}

    if(requestedLevel < 1 ||  requestedLevel > 99){
    }else{
      Serial.print(F("Changing level to "));
      Serial.print( byte(requestedLevel) );
      Serial.print(F(", from "));
      Serial.println( moistureThresholds[sensorID] );
      moistureThresholds[sensorID] = byte(requestedLevel);
      saveState(sensorID, moistureThresholds[sensorID]);

      drawItem(sensorID); // Finally, update the screen.
    }
    
    // Inform the gateway of the current DimmableLED's SwitchPower1 and LoadLevelStatus value...
    //send(lightMsg.set(currentLevel > 0));

    // hek comment: Is this really nessesary?
    //send( dimmerMsg.set(currentLevel) );

  }
}

#ifdef HAS_SCREEN
void drawItem(byte selectedSensor)
{
  // Prepare variables to send to the screen.
  byte verticalPosition = selectedSensor*18+28;
  Serial.println(verticalPosition);
  byte color = 10;  // green
  String spacer = F("");
  if( moistureLevels[selectedSensor] < 10){spacer = F(" ");}
  if(moistureLevels[selectedSensor] <= moistureThresholds[selectedSensor]){ // set the font color. Red if the plan is thirsty.
    color = 1;      // red
  }else if(moistureLevels[selectedSensor] <= moistureThresholds[selectedSensor] + 5){ // black for normal
    color = 13;     // orange
  }
  
  // Create graph
  Serial.print(F("/// Creating graph for: ")); Serial.println(selectedSensor);
  mySerial.print(F("BOXF(25,"));
  mySerial.print(String(verticalPosition + 1) + "," + String(25 + moistureLevels[selectedSensor])  + "," + String(verticalPosition + 16) + "," + String(color) + ");");
  mySerial.print("BOXF(" + String(25 + moistureLevels[selectedSensor] + 1) + ","  + String(verticalPosition + 1) + ",124," + String(verticalPosition + 16) + ",7);");
  
  mySerial.print("PL(" + String(25 + moistureThresholds[selectedSensor]) + "," + String(verticalPosition + 3) + "," + String(25 + moistureThresholds[selectedSensor])  + "," + String(verticalPosition + 14) + ",8);"); // Small vertical line that indicates the threshold
  
  mySerial.println("DCV16(5," + String(verticalPosition) + "," + String(spacer) + String(moistureLevels[selectedSensor]) + "," + String(color) + ");"); // Moisture level number on the left
  //mySerial.println();
  //String screenCommand = F("DCV16(5,");
  //screenCommand += String(verticalPosition) + F(" ,");
  //screenCommand += String(moistureLevel) + F(", ") + String(color) + F(");");
  //Serial.println(screenCommand);
  //mySerial.print(screenCommand);
  //wait(200); 
  
}
#endif


/* THANKS TO
 * 
 * The MySensors Arduino library handles the wireless radio link and protocol
 * between your home built sensors/actuators and HA controller of choice.
 * The sensors forms a self healing radio network with optional repeaters. Each
 * repeater and gateway builds a routing tables in EEPROM which keeps track of the
 * network topology allowing messages to be routed to nodes.
 *
 * Created by Henrik Ekblad <henrik.ekblad@mysensors.org>
 * Copyright (C) 2013-2015 Sensnology AB
 * Full contributor list: https://github.com/mysensors/Arduino/graphs/contributors
 *
 * Documentation: http://www.mysensors.org
 * Support Forum: http://forum.mysensors.org
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 */
