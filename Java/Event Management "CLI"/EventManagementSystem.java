package EventManagement;

import java.io.*;
import java.util.*;



public class EventManagementSystem {

    public static void menu() {
        // Creating a menu for chose a options from user/keyboard..........
        System.out.println("What you want to do my buddy");
        System.out.println("1. Add Event's Name");
        System.out.println("2. Show event's Details");
        System.out.println("3. Edit the event ");
        System.out.println("4. Delete any event ");
        System.out.println("5. You wanna exit the system ?");

    }

    /**
     * @param args
     * @throws Exception
     */
    @SuppressWarnings({ "unchecked", "resource" })
	public static void main(String args[]) throws Exception {
        Scanner in=new Scanner(System.in); //to take a  integer value form user or keyboard (in is for interger) 
        Scanner sc = new Scanner(System.in); // to take Sting value from user or keyboard (Sc for string)

//        creating array list 
        ArrayList <Event> arr = new ArrayList<Event>();

//        Event*list text file is made and data is stored there 
        File file = new File("Eventlist.txt");
        ObjectOutputStream wData = null;  //For wrote a data in file
        ObjectInputStream rData = null;
        if (file.isFile())
        {
        	rData = new ObjectInputStream (new  FileInputStream(file));
        	arr = (ArrayList<Event>)rData.readObject();
        	rData.close();
        }

        int choose = -1;
        do{
            menu();
            System.out.println("How do you want to manage your event sir/maam ?");
            choose = in.nextInt();

            switch(choose)
            {
                case 1:
      
                    System.out.println("Enter event title ");
                    String name=sc.nextLine();  //to get the input as string
                    System.out.println("Enter the address the event gonna happen:");
                    String address = sc.nextLine() ;
                    System.out.println("Enter event orgranizer phone number ");
                    String number = sc.nextLine();
                    System.out.println("Enter Event time and date:");
                    String timedate = sc.nextLine();
                    
                    //call array
                    arr.add(new Event( name, address, number, timedate)); //using Construction method;
                    wData= new ObjectOutputStream( new FileOutputStream(file));
                    wData.writeObject(arr);
                    wData.close();
                    System.out.println("Your event have been added to our system");
                    break;
                    
                    


                case 2:
                {
                	ListIterator<Event> il = arr.listIterator();
                	while(il.hasNext()) {
                		Event eve = il.next();
                		System.out.println("The event name : " + eve.getname() +"\n" + "The event address : " + eve.getaddress() +"\n" + "The date of the event : "  + eve.gettimedate() + "\n" + "Number of event orgranizer : "+ eve.getnumber() +"\n" + "\n" +"Another event is :" + "\n");
                	}
                }
                System.out.println("These all are your events.");
                	break;
                case 3:
                    // Code for editing an event
                    System.out.println("Enter the event title you want to edit:");
                    String editTitle = sc.nextLine();

                    // Find the event with the given title
                    boolean eventFound = false;
                    for (int i = 0; i < arr.size(); i++) {
                        Event event = arr.get(i);
                        if (event.getname().equalsIgnoreCase(editTitle)) {
                            System.out.println("Enter new event title:");
                            String newName = sc.nextLine();
                            System.out.println("Enter new address:");
                            String newAddress = sc.nextLine();
                            System.out.println("Enter new organizer phone number:");
                            String newNumber = sc.nextLine();
                            System.out.println("Enter new event time and date:");
                            String newTimeDate = sc.nextLine();

                            // Update the event details by creating a new Event object
                            Event updatedEvent = new Event(newName, newAddress, newNumber, newTimeDate);
                            arr.set(i, updatedEvent);

                            wData = new ObjectOutputStream(new FileOutputStream(file));
                            wData.writeObject(arr);
                            wData.close();

                            System.out.println("Event updated successfully.");
                            eventFound = true;
                            break;
                        }
                    }

                    if (!eventFound) {
                        System.out.println("Event not found.");
                    }
                    break;
                
                case 4:
                    // Code for deleting an event
                    System.out.println("Enter the event title you want to delete:");
                    String deleteTitle = sc.nextLine();

                    // Create an iterator to safely remove elements
                    Iterator<Event> iterator = arr.iterator();
                    boolean eventDeleted = false;

                    while (iterator.hasNext()) {
                        Event event = iterator.next();
                        if (event.getname().equalsIgnoreCase(deleteTitle)) {
                            iterator.remove(); // Safely remove the event
                            eventDeleted = true;
                            break; // Exit the loop after the event is deleted
                        }
                    }

                    // Write the updated list of events (with the deleted event removed) back to the file
                    wData = new ObjectOutputStream(new FileOutputStream(file));
                    wData.writeObject(arr);
                    wData.close();

                    if (eventDeleted) {
                        System.out.println("Event deleted successfully.");
                    } else {
                        System.out.println("Event not found.");
                    }
                    break;
                case 5:
                    System.exit(0);
            }

        } while (choose != 0);

        
    }

}
