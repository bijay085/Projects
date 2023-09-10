package EventManagement;

import java.io.Serializable;



@SuppressWarnings("serial")
public class Event implements Serializable {
	

    private String name;
    private String address;
    private String number;    // phone number
    private String timedate;

    public Event()
{

    name = "";
    address = "";
    number = "";
    timedate = "";
}


    public Event( String name, String address, String number, String timedate) {
        

        this.name = name;
        this.address = address;
        this.number = number;
        this.timedate = timedate;
    }


    public String getname(){
        return name;
    }
    public String getaddress() {
        return address;
    }
    public String getnumber(){
        return number;
    }
    public String gettimedate(){
        return timedate;
    }
    

}

