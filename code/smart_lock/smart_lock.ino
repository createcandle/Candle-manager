/*
 * DESCRIPTION
 * 
 * This is a MySensors node that can toggle two relays in two ways: normally, or via SMS.
 * The SMS function is password protected: you have to send a password to switch the relay.
 * 
 * I use it as door lock system. One lock opens 3 seconds when it gets a signal, the other switches from locked to unlocked (and vice versa).
 * 
 * The node also creates two password fields. The idea is that you can change this text value on your controller (Domoticz for example).
 * The node then periodically polls the controller to ask for the current value of this text, which it uses as the password. 
 * 
 * It also has a backup password, which is hardcoded in the node. This password only becomes useable if the node detects that it has lost connection to the controller.
 * 
 * The node also has two more text outputs which are used to (1) log the SMSs it receives, and (2) which phonenumbers sent the SMS. This way you can get some insight, and use this in other parts of your system.
 * 
 * _Possible improvements_
 * - for increased safety you could limit which phonenumbers are even allowed to send commands. This could also be a comma-separated text value on the controller.
 * - currently both relays share the password, and the second relay just has a '2' added to the password. This could easily be separated out.
 * 
 * Lots of useful commands: https://github.com/stephaneAG/SIM800L/blob/master/README.md
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * SETTINGS */

char phone1[14] = "_31123456789";                   // Phone number of user #1

char phone2[14] = "_31123456789";                   // Phone number of user #2 (optional). If there is no second user, set the same number as user #1 again.

char rotatingPassword1[26] = "door1";               // What should be the password for door #1? If the device loses power and can't reconnect to the network, then this will be the fallback password. Maximum length is 25 characters.

char rotatingPassword2[26] = "door2";               // What should be the password for door #2?

/* END OF SETTINGS
  *  
  *  
*/

//v4
//NOTE TO SELF: moet vooral nog even checken hoe het zit met het aanmaken van textvariabelen (en switches) op Domoticz. ONtstaan die vanzelf, of moet de node een 'eerste waarde' sturen voordat het in de interface verschijnt.
// note to self: zitten twee functies in om ram gebruik checken
//note to self: in de receiver zitten eeprom storage experimenten.

//v5
// added char instead of string

//v6 works!
// S_LOCK experiment

//v7
// Switching over to the Electrow relay board.


//#define DEBUG true    //Open the debug information 



//
// SETTINGS
//

// Enable and select the attached radio type
#define MY_RADIO_RF24                              // This is a common and simple radio used with MySensors. Downside is that it uses the same frequency space as WiFi.
//#define MY_RADIO_NRF5_ESB                         // This is a new type of device that is arduino and radio all in one. Currently not suitable for beginners yet.
//#define MY_RADIO_RFM69                            // This is an open source radio on the 433mhz frequency. Great range and built-in encryption, but more expensive and little more difficult to connect.
//#define MY_RADIO_RFM95                            // This is a LoRaWan radio, which can have a range of 10km.

// MySensors: Choose your desired radio power level. High power can cause issues on cheap Chinese NRF24 radio's.
//#define MY_RF24_PA_LEVEL RF24_PA_MIN
//#define MY_RF24_PA_LEVEL RF24_PA_LOW
#define MY_RF24_PA_LEVEL RF24_PA_HIGH
//#define MY_RF24_PA_LEVEL RF24_PA_MAX

// Mysensors security
//#define MY_SECURITY_SIMPLE_PASSWD "changeme"      // Be aware, the length of the password has an effect on memory use.
#define MY_SIGNING_SOFT_RANDOMSEED_PIN A0         // Setting a pin to pickup random electromagnetic noise helps make encryption more secure.

// Mysensors advanced settings
#define MY_TRANSPORT_WAIT_READY_MS 10000            // Try connecting for 10 seconds. Otherwise just continue.
//#define MY_RF24_CHANNEL 100                       // In EU the default channel 76 overlaps with wifi, so you could try using channel 100. But you will have to set this up on every device, and also on the controller.
//#define MY_RF24_DATARATE RF24_1MBPS               // Slower datarate makes the network more stable?
//#define MY_NODE_ID 10                             // Giving a node a manual ID can in rare cases fix connection issues.
//#define MY_PARENT_NODE_ID 0                       // Fixating the ID of the gatewaynode can in rare cases fix connection issues.
//#define MY_PARENT_NODE_IS_STATIC                  // Used together with setting the parent node ID. Daking the controller ID static can in rare cases fix connection issues.
#define MY_SPLASH_SCREEN_DISABLED                   // Saves a little memory.
//#define MY_DISABLE_RAM_ROUTING_TABLE_FEATURE      // Saves a little memory.

// Enable MySensors debug output to the serial monitor, so you can check if the radio is working ok.
//#define MY_DEBUG 

// MySensors devices form a mesh network by passing along messages for each other. Do you want this node to also be a repeater?
#define MY_REPEATER_FEATURE                         // Add or remove the two slashes at the beginning of this line to select if you want this sensor to act as a repeater for other sensors. If this node is on battery power, you probably shouldn't enable this.

#define MY_RF24_CE_PIN 7
#define MY_RF24_CS_PIN 6


// Define Node ID
//#define MY_NODE_ID 15
//#define MY_PARENT_NODE_ID 0
//#define MY_PARENT_NODE_IS_STATIC


// Allow the smart lock to send you an SMS once every 49 days? It's better to let your controller trigger this, but it's a nice option to have.
// #define SEND_SMS_EVERY_49_DAYS                      //  This can help keep the simcard 'alive'. The first sms will be sent a week after the smart lock powers on.

// THESE VALUES CAN BE CHANGED
#define RELAY1_PIN 2                                // relay 1 pin number (use this on a door that needs a short pulse to open).
#define RELAY2_PIN 3                                // relay 2 pin number (use this on a door that can be set to locked or unlocked mode, for example with a solenoid.).
// In theory you could add two more locks. Pins 2,3,4 and 5 are the relays.
#define BUTTON_PIN 5                                // toggle pin number. This can be used to set 'home' and 'away' status in your system.
#define LED_PIN 6                                   // pin for an optional status LED.
#define RELAY_ON 1                                  // GPIO value toc write to turn on attached relay
#define RELAY_OFF 0                                 // GPIO value to write to turn off attached relay
#define DOOR1OPENTIME 3000                          // how long the pulse should last (how long the relay should be on if a signal is received).(3000 = 3 seconds)
#define LOOPDURATION 4000                           // the main loop runs every x milliseconds. This main loop starts the modem, and from then on periodically requests the password.
#define LOOPSBETWEENPASSWORDREQUESTS 45             // 45 * 4seconds = 3 minutes. So every 3 minute the node asks if the password is still the same.
#define MAXIMUMTIMEOUTS 3                           // how often the network may fail before we conclude there is a connection problem.
#define LOCKED 1                                    // shorthand for apartment door. Some relays work in reverse..
#define UNLOCKED 0                                  // shorthand for apartment door. Some relays work in reverse.. 



//
// Do not change below this line
// 

#include <MySensors.h>                              // The MySensors library that allows devices to communicate and form networks.
#include <avr/pgmspace.h>
//#include <EEPROM.h>
//#include <MemoryFree.h>;
//#include <pgmStrToRAM.h>; // not needed for new way. but good to have for reference.

#include <SoftwareSerial.h>                         // A software library for serial communication. In this case we use it so talk to the SIM800L GSM modem.
SoftwareSerial gsm(7,8);                            // Receive pin (RX), transmit pin (TX)

#define DEVICE_STATUS_ID 1                          // The first 'child' of this device is a text field that contains status updates.
#define SMS_CHILD_ID 2                              // This fiels is used to tell the controller what text was in the sms. Useful for automation of other things that you want to trigger using an SMS.
#define ROTATING_PASSWORD1_ID 3                     // Set password ID number on this node that can be set from outside.
#define ROTATING_PASSWORD2_ID 4                     // Set password ID number on this node that can be set from outside.
#define RELAY1_CHILD_ID 5                           // Set switch ID number on this node.
#define RELAY2_CHILD_ID 6                           // Set switch ID number on this node.
#define BUTTON_CHILD_ID 7                           // Set button ID number on this node.
#define SENDSMS_CHILD_ID 8                          // If this button is pressed at the controller, a test sms will be sent. Useful to keep a simcard alive (must send at least one SMS every X months usually).
#define PHONENUMBER1_ID 9                           // If (part of) a phonenumber is given, only phonenumbers that have that part will be allowed to open the lock. E.g. "+31" only allows Dutch numbers. "+3161482375" would only allow that one number.
#define PHONENUMBER2_ID 10

MyMessage charmsg(DEVICE_STATUS_ID, V_TEXT);        // Sets up the message format that we'll be sending to the MySensors gateway later. The first part is the ID of the specific sensor module on this node. The second part tells the gateway what kind of data to expect.
MyMessage relaymsg(RELAY1_CHILD_ID, V_LOCK_STATUS); // EXPERIMENT, try V_TRIPPED or V_STATUS or V_LOCK_STATUS  // is used by the relays and the toggle to send their status to Domoticz.
byte timeOutCount = 0;                              // How often did we not get the password back after we requested it? If it's too often, then the connection is down.

boolean flag = 0;                                   // Used in flow control when processing an SMS.
boolean desiredDoor1state = 0;                      // Is relay 2 in an open state? Front door starts closed
boolean desiredDoor2state = UNLOCKED;               // What does the controller want?
boolean actualDoor2state = UNLOCKED;                // What does the controller want?
boolean waitingForResponse = false;                 // Used to check if the connection to the controller is ok. If we are still waiting by the next time a password is requested, something is fishy.

#define RF_DELAY 100                                // Milliseconds betweeen radio signals during the presentation phase.

int freeRam () {
  extern int __heap_start, *__brkval; 
  int v; 
  return (int) &v - (__brkval == 0 ? (int) &__heap_start : (int) __brkval); 
}
// via https://playground.arduino.cc/Code/AvailableMemory

void sendStatusSMS()
{
  // send an sms
  //if(sizeof(phone1) > 9){                        // Check if we have received a real phone number to work with.
    Serial.println(F("sending test sms"));
    Serial1.print(F("AT+CMGS=\""));
    Serial1.print(phone1);                      
    Serial1.println(F("\""));
    wait(100);
    if(actualDoor2state == LOCKED){
      Serial.println(F("Door 2 is locked"));
    }else{
      Serial.println(F("Door 2 is unlocked"));
    }
    send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("Sent an SMS")));  
  //}
}


void before()
{
  for(int i=2;i<6;i++){ // initialize the Relay pins status:
    pinMode(i,OUTPUT);
    digitalWrite(i,LOW);
  }
  
/*
  pinMode(RELAY1_PIN, OUTPUT);
  digitalWrite(RELAY1_PIN, LOW);                    // This is the initial state of the relays. You may want to change this!
  pinMode(RELAY2_PIN, OUTPUT);
  digitalWrite(RELAY2_PIN, LOW);                    // this is the initial state of the relays. You may want to change this!
*/
  //pinMode(BUTTON_PIN, INPUT_PULLUP);
  //pinMode(LED_PIN, OUTPUT);  
  //digitalWrite(LED_PIN, HIGH);  
}


void presentation()
{
  sendSketchInfo(F("SMS & password relays"), F("1.64")); wait(RF_DELAY);
  /*
  present(DEVICE_STATUS_ID, S_INFO, "lock status"); wait(RF_DELAY);
  present(SMS_CHILD_ID, S_INFO, "received sms"); wait(RF_DELAY);
  present(ROTATING_PASSWORD1_ID, S_INFO, "door 1 password"); wait(RF_DELAY);
  present(ROTATING_PASSWORD2_ID, S_INFO, "door 2 password"); wait(RF_DELAY);
  present(RELAY1_CHILD_ID, S_LOCK, "door 1", true); wait(RF_DELAY);       // 
  present(RELAY2_CHILD_ID, S_LOCK, "door 2", true); wait(RF_DELAY);      // S_BINARY could be S_LOCK instead?
  present(BUTTON_CHILD_ID, S_LOCK, "toggle status"); wait(RF_DELAY);// this could be something else, so that it doesn't register as a button in the interface? Or.. register it as a pushbutton?
  present(SENDSMS_CHILD_ID, S_INFO, "SMS to send"); wait(RF_DELAY);
  */
  present(DEVICE_STATUS_ID, S_INFO, F("Status")); wait(RF_DELAY);
  present(SMS_CHILD_ID, S_INFO,F("Received SMS")); wait(RF_DELAY);
  present(ROTATING_PASSWORD1_ID, S_INFO,F("Password 1")); wait(RF_DELAY);
  present(ROTATING_PASSWORD2_ID, S_INFO,F("Password 1")); wait(RF_DELAY);
  present(RELAY1_CHILD_ID, S_LOCK, F("Lock 1"), true); wait(RF_DELAY);
  present(RELAY2_CHILD_ID, S_LOCK, F("Lock 2"), true); wait(RF_DELAY); // Should it use S_BINARY or S_LOCK?
  present(BUTTON_CHILD_ID, S_LOCK,F("hello-goodbye button")); wait(RF_DELAY); // this could be something else, so that it doesn't register as a button in the interface? Or.. register it as a pushbutton?
  present(SENDSMS_CHILD_ID, S_INFO,F("Change this to send SMS to #1")); wait(RF_DELAY);
  present(PHONENUMBER1_ID, S_INFO,F("Phone number 1")); wait(RF_DELAY);
  present(PHONENUMBER2_ID, S_INFO,F("Phone number 1")); wait(RF_DELAY);
}

void setup()
{
  wait(2000);
  Serial.begin(115200);
  Serial.println(F("Hello world, I am a SMS door lock."));
  Serial1.begin(19200);

  //Power on the SIM800C (without having to press the button)
  pinMode(9,OUTPUT);
  digitalWrite(9,HIGH);
  wait(3000);
  digitalWrite(9,LOW);
  wait(1000);
  
  //sendData("AT",2000,DEBUG);
  //sendData("AT+CMGF=1",1000,DEBUG);        //Set the SMS in text mode

  
  // has a connection to the controller been established?
  if(isTransportReady()){
    Serial.println(F("Connected to gateway!"));
  }else{
    Serial.println(F("WARNING, NOT CONNECTED TO GATEWAY"));
    timeOutCount = MAXIMUMTIMEOUTS;                 // start the system in 'no connection' mode, meaning the backup passwords may be used.
  }
  
  Serial.println(F("Connecting to GSM.."));
  Serial1.begin(9600);
  wait(5000);

  wdt_enable(WDTO_8S);                              // Starts the watchdog timer. If it is not reset once every 2 seconds, then the entire device will automatically restart.                                
  
}


void loop()
{
  static bool buttonBeingPressed = 0;               // used to debounce the button.

/*
  if(Serial1.available()>0){
  Serial.write(Serial1.read());
      
    if(Serial1.find(target)){                  //If receive a new SMS
       sms_no = Serial1.parseInt();            //Get the SMS ID        
       get_message = "AT+CMGR="+(String)sms_no; //The command of the content of the SMS
       Serial.println(("******************** Print the relay status *********************"));
       Data_handling(get_message,500,DEBUG);    //Get the content of the SMS 
       Serial.println(F("*****************************END*********************************"));
    } 
   
  }
  while(Serial1.read() >= 0){}                     // Clear serial buffer   
*/




/* 
  if(Serial1.available() > 0){
    msg = Serial1.readStringUntil('\n');
    Serial.println(msg);
    processline();
  }
*/
 
  if(Serial1.available() > 0)
  {
    processline();
  } 





#ifdef SEND_SMS_EVERY_49_DAYS
  // This is an optional feature.
  // Every 49,7 days (using the millis() rollover) the system sends out an SMS. This helps keep the simcard active and registered on the GSM network. 
  // Use at your own risk: if the system experiences a power loss, the timer starts at 0 again. If your experience frequent powerlosses, then the simcard might be-deregistered anyway, since the keep-alive SMS's won't get sent. 
  // A slight delay is built in: the first sms is sent a week after the smart lock becomes active. This avoid sending a lot of SMS's if you are still playing with setting up the device, and powerlosses may be frequent.
  // This smart lock also offers another option. You can let the controller trigger the sending of the sms using the 'send test sms' button. Of course, your controller could also be down when you scheduled to trigger the button.
  static bool keepAliveSMSsent = false;             // used to make sure an SMS is only sent as the milliseconds starts, and not during every loop in the millisecond.
  if(millis() < 5){
    keepAliveSMSsent  = false;                      // when the clock rolls over, set the variable back so that a new SMS can be sent.
  }
  if (millis() > 604800000 && millis() < 604800010 && keepAliveSMSsent == false){ // 604800000 = 1 week in milliseconds. Sending the first keep-alive SMS after a week avoids sending a lot of SMS-es while testing the system (which may involve a lot of reboots).
    keepAliveSMSsent  = true;
    sendStatusSMS();
  }
#endif

  /*
   * // test functions
   * 
    if( (millis() % 4000) == 0 ) { // test function. for easy testing, also comment out the sms-delete line further down.
    //Serial.println("_____");
    while (Serial1.available())
    Serial1.read();
    msg = "";
    Serial1.write("AT+CMGR=1\r\n"); // get first SMS on the phone
    wait(1);
    }

    useful for quick debugging and playing with the GSM modem.
    if(Serial.available()){    
      Serial1.write(Serial.read());
    }
  */

  //
  // MAIN LOOP
  // runs every few seconds. By counting how often this loop has run (and resetting that counter back to zero after 250 loops), it becomes possible to schedule all kinds of things without using a lot of memory.
  // maximum time that can be scheduled is 4s * 250 loops = 1000 seconds. So the entire things runs approximately every 16 minutes.
  //

  static byte loopCounter = 0;                            // Count how many loops have passed (reset to 0 after at most 254 loops).
  static boolean loopDone = false;                        // used to make sure the 'once every millisecond' things only run once every millisecond (or 2.. sometimes the millis() function skips a millisecond.);

  // allow the next loop to only run once. This entire construction saves memory by not using a long to store the last time the loop ran.
  if( (millis() % LOOPDURATION) > LOOPDURATION - 4 && loopDone == true ) {
    loopDone = false;  
  }

  // Main loop to time actions.
  if( (millis() % LOOPDURATION) < 4 && loopDone == false ) { // this module's approach to measuring the passage of time saves a tiny bit of memory.
    loopDone = true;
  //if (millis() - lastLoopTime > LOOPDURATION) {
    //lastLoopTime = millis();
    loopCounter++;

    wdt_reset();                                          // Reset the watchdog timer
    void sendHeartbeat();                                 // officially tell the controller that the device is still active. Not all controllers support this, but it can' hurt.

    // schedule
    switch (loopCounter) {
      case 1:    
        Serial1.println(F("AT"));                             // initiating the GSM modem
        //Serial.println(F("(1) Requesting phone numbers"));    // if they don't exist on the controller, then something will be sent to the controller just to make sure that the phonenumber fields will register in its interface. // is this really necessary? How did the password fields show up then?
        request(PHONENUMBER1_ID, V_TEXT);                 // periodically check which phone numbers are allowed. By requesting this very early we can later on avoid overwriting any values that the user may have set.
        request(PHONENUMBER2_ID, V_TEXT);                 // periodically check which phone numbers are allowed.
        break;
      case 2:    
        Serial.println(F("(2) sending AT again, and asking for password."));
        Serial1.println(F("AT"));                             // handshake the modem again, just to be safe.
        request(ROTATING_PASSWORD1_ID, V_TEXT);           // periodically check if there is a new password set.
        request(ROTATING_PASSWORD2_ID, V_TEXT);           // periodically check if there is a new password set.
        break;
      case 3:    
        //Serial1.println(F("AT+COPS?"));                     // report network connection status
        if(strcmp(rotatingPassword1, "door1") == 0 && strcmp(rotatingPassword2, "door2") == 0 ){ // here we assume the controller has no password set yet (or doesn't support this)
          Serial.println(F("sending default passwords to controller"));
          send(charmsg.setSensor(ROTATING_PASSWORD1_ID).set(rotatingPassword1));
          wait(100);
          send(charmsg.setSensor(ROTATING_PASSWORD2_ID).set(rotatingPassword2));
        }else{
          Serial.println(F("not sending default passwords to controller"));
        }
        break;
      case 4:
        //Serial.println(F("(5) set to sms mode"));
        Serial1.println(F("AT+CMGF=1"));                      // switch phone to SMS mode
        break;
      case 5:    
        //Serial.println(F("(10) listen for SMS"));
        Serial1.println(F("AT+CNMI=2,2,0,0,0"));              // listen to incoming messages. 
        break;
      case 7:
        //Serial.println(F("(7) del all"));
        Serial1.println(F("AT+CMGDA=\"DEL ALL\""));           // delete all old SMS messages (can take some time if there are many)
        break;

      // Cases 15 and above happen every few minutes
      case 16:    
        //Serial.println(F("(16) Requesting phone numbers"));
        request(PHONENUMBER1_ID, V_TEXT);                 // periodically check which phone numbers are allowed to control the smart lock.
        request(PHONENUMBER2_ID, V_TEXT);                 // periodically check which phone numbers are allowed to control the smart lock.
        break;
      case 17:    
        //Serial.println(F("(17) Requesting the current passwords"));
        request(ROTATING_PASSWORD1_ID, V_TEXT);           // periodically check if there is a new password set.
        request(ROTATING_PASSWORD2_ID, V_TEXT);           // periodically check if there is a new password set.
        waitingForResponse = true;                        // used to check if the connection with the controller is ok.
        break;
      case 18:  
        //Serial.println(F("(18) Free RAM = ")); //F function does the same and is now a built in library, in IDE > 1.0.0
        //Serial.println(freeRam()); // this is a smaller, alternative function to check free memory.
        break;
    }

    // after booting the system we skip sending the AT commands again, and go straight to re-requesting the latest passwords.
    if(loopCounter > 15 + LOOPSBETWEENPASSWORDREQUESTS){
      loopCounter = 15;
    }

    //Serial.print(F("loopcounter = ")); + Serial.println(loopCounter);
    
    if(waitingForResponse == true){
      if(timeOutCount < MAXIMUMTIMEOUTS){                 
        timeOutCount++;                                   // server failed to give the password in time. We're still waiting.. so connection problems?
      }else{
        Serial.println(F("! CONNECTION LOST?"));          // server failed to give us the current password a few times. The server must be down.
      }
    }
  }

  if (desiredDoor1state == true) {
    if(digitalRead(RELAY1_PIN) == RELAY_OFF){             // the first door has just now been set to open. We hijack the password request timer to use it for a non-blocking timer.
      //Serial.println(F("door 1 pulse.."));
      send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("Door 1 opened") ));
      digitalWrite(RELAY1_PIN, RELAY_ON);
      wait(1000);                                        // this makes the node a little blocking.. but this occurance should be rare..
      digitalWrite(RELAY1_PIN, RELAY_OFF);                // re-lock the door
      send(relaymsg.setSensor(RELAY1_CHILD_ID).set(LOCKED));   // tell the controller the door is back in locked state. Could the node just tell the controller this is a pulse button?
      desiredDoor1state = false;

      //Serial.println(F("Free RAM = ")); //F function does the same and is now a built in library, in IDE > 1.0.0
      //Serial.println(freeRam()); // this is a smaller, alternative function to check free memory.

    }
  }



  // dealing with relay 2 - the apartment door. Decide what to do if a change has been requested.
  if (actualDoor2state != desiredDoor2state){
    
    if(desiredDoor2state == LOCKED){
      digitalWrite(RELAY2_PIN, LOCKED);
      send(relaymsg.setSensor(RELAY2_CHILD_ID).set(LOCKED));   // let the controller know about the state.
      wait(100);      
      send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("Door 2 locked") ));
      //Serial.println(F("2 locked"));
    }else{
      digitalWrite(RELAY2_PIN, UNLOCKED);
      send(relaymsg.setSensor(RELAY2_CHILD_ID).set(UNLOCKED));
      wait(100);
      send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("Door 2 Unlocked") ));
      //Serial.println(F("2 Unlocked"));
    }
    actualDoor2state = desiredDoor2state;
  }


  // check button to toggle the state
  if (digitalRead(BUTTON_PIN) == LOW){
    if(buttonBeingPressed == 0){
      desiredDoor2state = !desiredDoor2state; // on press of the button, togle the door 2 desired status. e.g. locked -> unlocked.
      send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("Button pressed") ));
      Serial.print(F("Button->"));
      Serial.println(desiredDoor2state);
    }
    buttonBeingPressed = 1;
  }
  else{
    buttonBeingPressed = 0; 
  }

}


// This parses the data from the GSM modem, and filters out the actual SMS text
void processline() {

  // first, get data from the serial buffer.

  char serialLine[26];                              // experiment with using a char array instead of a string.. again.
  byte number_of_bytes_received;                    // was int instead of byte.
 
  //Serial.println(F("x"));
  number_of_bytes_received = Serial1.readBytesUntil('\n',serialLine,25); // read bytes (max. 50) from buffer, untill <CR> (13). Store bytes in data. Count the bytes recieved.
  //Serial.println(number_of_bytes_received);
  serialLine[number_of_bytes_received] = 0;         // Add a 0 terminator to the char array
  //Serial.println(serialLine);
  
  Serial.print(F("__processing line: ")); Serial.println(serialLine);
  //Serial.print(F("flag: ")); Serial.println(flag);
  // if the flag has been set, that means we already found the phonenumber part of the sms, and are now at the actual sms content part.
  if ( flag == 1 ) {
    flag = 0; // reset for when the next sms needs to be disected.
    send(charmsg.setSensor(SMS_CHILD_ID).set(serialLine));
    Serial.print(F("SMS: ")); Serial.println(serialLine);

    // Does the SMS content begin with a correct password?
    byte password1length = strlen(rotatingPassword1);
    byte password2length = strlen(rotatingPassword2);
    //Serial.print(F("pass 1 length: "));Serial.println(password1length);
    //Serial.print(F("serialline: "));Serial.println(serialLine);
    //Serial.print(F("rotatingpassword: "));Serial.println(rotatingPassword1);
    bool password1found = strncmp(serialLine, rotatingPassword1, password1length);
    bool password2found = strncmp(serialLine, rotatingPassword2, password2length);
    Serial.print(F("pass 1 found?: "));Serial.println(password1found);
    Serial.print(F("pass 2 found?: "));Serial.println(password1found);
    
    if (password1found == 0) {
      Serial.println(F("Password correct. Opening door 1."));
      desiredDoor1state = true;
    }

    if (password2found == 0) {
      Serial.println(F("Password 2 correct."));
      // next, check for a specific command.
      char * command;
      command = strstr(serialLine, " ");      // search for the index of the space
      if (command != NULL)                     // if successful then command now points to the second word in the SMS
      {
        Serial.print(F("Command = ")); Serial.println(command);
        // check what the command is, and act on it.
        bool commandLock = strncmp (command, " lock", 5);
        bool commandUnlock = strncmp (command, " unlock", 7);
        bool commandStatus = strncmp (command, " status", 7);
        if(commandLock == 0){ desiredDoor2state = LOCKED; Serial.println(F("command: lock door 2")); }
        if(commandUnlock == 0){ desiredDoor2state = UNLOCKED; Serial.println(F("command: unlock door 2")); }
        if(commandStatus == 0){ sendStatusSMS(); Serial.println(F("command: send status sms")); }

        //Serial.println(F("password compare Free RAM = ")); //F function does the same and is now a built in library, in IDE > 1.0.0
        //Serial.println(freeRam()); // this is a smaller, alternative function to check free memory.

      }                                  
    }
    Serial1.println(F("AT+CMGDA=\"DEL ALL\"")); // delete all received sms messages.

    //Serial.println(F("Free RAM = ")); //F function does the same and is now a built in library, in IDE > 1.0.0
    //Serial.println(freeRam()); // this is a smaller, alternative function to check free memory.

    
  } else {

    // phone number check
    
    bool atPhoneNumberLine = strncmp(serialLine, "+CMT", 4); // Are we at the part of the sms that holds the sender's phonenumber?
    //Serial.print(F("CMT found?")); Serial.println(atPhoneNumberLine);
    // now let's check if the correct phonenumbers are used.
    // hmm, perhaps wise and simple to just always compare the two phonenumber strings to the serial line. If the phonenumberstring are "", then this will be considered "found", and still allow the flag to be set? 
    // oF werken deze comparison dingen alleen met nul terminators? Ook al zijn het niet echt string maar char. Ik las zoiets..
    
    if (atPhoneNumberLine == 0){
      Serial.println(F("+CMT found"));
      // Can we find a phonenumber in the serial line?
  
      
      // source: https://forum.arduino.cc/index.php?topic=432185.0
      // alt https://forum.arduino.cc/index.php?topic=16824.0

      // check if the phonenumbers can be found
      char * p;
      char * q;
      Serial.print(F("phone#1 to check against = ")); Serial.println(phone1);
      Serial.print(F("phone#2 to check against = ")); Serial.println(phone2);
      p = strstr (serialLine, phone1);
      q = strstr (serialLine, phone2);
      if (p || q) {
        Serial.println(F("Phone number is allowed"));
        flag = 1;   // we may now proces the sms content, which will be on the next serial line.

        char foundNumber[13];
        memcpy( foundNumber, &serialLine[10], 12 ); // ? starts at position 10 and then copies 4 characters?
        foundNumber[12] = '\0';
        
        // perhaps the top solution works. If not, the one below will (but creates a new temporary variable)
        //send(charmsg.setSensor(SMS_CHILD_ID).set( "%.*s", 4, serialLine + 10 ));
        send(charmsg.setSensor(SMS_CHILD_ID).set( foundNumber ));
        // alternatively, only send when a number is recognised.
        if(p){ send(charmsg.setSensor(SMS_CHILD_ID).set(F("Phonenumber 1:"))); }
        else if(q){ send(charmsg.setSensor(SMS_CHILD_ID).set(F("Phonenumber 2:"))); }
        //Serial.print("found ["); Serial.print(phoneNumber); Serial.print("] at position "); Serial.print((int) (p - GSMbuffer));

        //Serial.println(F("Free RAM = ")); //F function does the same and is now a built in library, in IDE > 1.0.0
        //Serial.println(freeRam()); // this is a smaller, alternative function to check free memory.
        
      } else {
        // didn't find either phone number. But in theory when the two phone number array's are "", the above should also trigger.
        // so this should never happen.
        Serial.println(F("I should never happen."));
      }

      /*
      char input;
      do{
        if(Serial.available()>0){
          input = Serial.read();
        }
      }while(input != '\n');
      */
      
      // remove the rest of the line in the buffer.
      Serial1.readBytesUntil('\n',serialLine,24);

    }
  



  
  //Serial1.readBytesUntil('\n');

  }



  // finally, clear the message array and proces the next line from the GSM modem.
  //for( int i = 0; i < sizeof(serialLine);  ++i ){
  // serialLine[i] = (char)0;
  //}
}

void receive(const MyMessage &message)
{

  timeOutCount = 0;
  
  //lastTimeConnected = millis();
  Serial.print(F("->receiving child ")); Serial.println(message.sensor);

  //if (message.type == V_STATUS) {
    // Change relay state

    if(message.sensor == RELAY1_CHILD_ID){
      desiredDoor1state = message.getBool();
      Serial.print(F("Controller -> door 1 ->")); Serial.println(desiredDoor1state); 
    }

    if(message.sensor == RELAY2_CHILD_ID){
      desiredDoor2state = message.getBool(); // ? RELAY_ON : RELAY_OFF;
      Serial.print(F("Controller -> door 2 ->")); Serial.println(desiredDoor2state); 
    }

    if(message.sensor == BUTTON_CHILD_ID){
      Serial.println(F("Controller -> button -> nothing")); // the toggle button should not be called from the side of the controller. Perhaps it should be turned into a percentage output sensor instead..
    }

  //}
  if (message.type == V_TEXT) {
    
    // receiving the rotating passwords.
    waitingForResponse = false;

    if(message.sensor == ROTATING_PASSWORD1_ID){
      if( rotatingPassword1 != message.getString() && sizeof(message.getString()) > 2 ){
        strcpy(rotatingPassword1, message.getString());
        //EEPROM.put(260,message.getString());              // if the password hasn't changed the put function won't actually overwrite the data (so no worries about wearing down the eeprom)
        Serial.print(F("Rotating password 1 is now: ")); Serial.println(rotatingPassword1);
        send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("received latest password 1")));
      }
    }
    else if(message.sensor == ROTATING_PASSWORD2_ID){
      if( rotatingPassword2 != message.getString() && sizeof(message.getString()) > 2 ){
        strcpy( rotatingPassword2, message.getString());
        //EEPROM.put(290,message.getString());   
        Serial.print(F("Rotating password 2 is now: ")); Serial.println(rotatingPassword2);
        send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("received latest password 2")));
      }
    }

    // receiving the (parts of) phonenumbers that are allowed to operate the smart lock.
    else if(message.sensor == PHONENUMBER1_ID){
      strcpy(phone1, message.getString());
      //EEPROM.put(320,phone1);        // MySensors strings can be 25 characters at most. Reserving 30 bytes should be ok?
      Serial.print(F("received phonenumber part 1: ")); Serial.println(phone1);
      send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("received phone 1")));
    }
    else if(message.sensor == PHONENUMBER2_ID){
      Serial.println(F("PHONE NUMBER"));
      strcpy(phone2, message.getString());
      //EEPROM.put(350,phone2); 
      Serial.print(F("received phonenumber part 2: ")); Serial.println(phone2);
      send(charmsg.setSensor(DEVICE_STATUS_ID).set( F("received phone 2")));
    }
        
    // if the 'send a test sms' button was pressed on the controller
    else if(message.sensor == SENDSMS_CHILD_ID){
      String SMStosend = message.getString();
      Serial.println(SMStosend);
      sendStatusSMS();
    }

  }
}

/*
void sendData(String command, const int timeout, boolean debug)  //Send command function
{
    String response = "";    
    Serial1.println(command); 
    long int time = millis();
    while( (time+timeout) > millis()){
      while(Serial1.available()){       
        response += (char)Serial1.read(); 
      }  
    }    
    if(debug){
      Serial.print(response);
    }    
}
*/
