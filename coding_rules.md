# Coding rules

## Eloquent Models vs DB Raw queries
Use as much as Eloquent Models / ORM. This will make the code clean readable as well as efficient.
For example there are two tables  for doctors and appointments viz 'doctors' and 'appointments'. We will have eloquent models for this in this way.
* Doctors 
```
class Doctor extends Eloquent
{

}
```

* Appointments 
```
class Doctor extends Eloquent
{

}
```
## Define ORM relationships precisely

Doctor to Appointments relationship
```
class Doctor extends Eloquent
{
   public function appointments()
   {
       return $this->hasMany(App\Models\Apointment::class, 'doctor_id');
   }
}
```

<br />

Appointments to Doctor relationship
```
class Appointment extends Eloquent
{
   public function appointments()
   {
       return $this->belongsTo(App\Models\Doctor::class);
   }
}
```

<br />

## Query Against Eloquent in the Laravel way

* Always use pagination for loading multiple rows, unless needed for small size table.
```
App\Models\Doctor::paginate(10);
```

* This will return a Laravel Collection, you can convert this collection to any data structure supported by Laravel. (toJson(), toArray() etc).

<br />

* It is always better to specify only required columns while quering against a table.
```
App\Models\Doctor::select('id','name','phone','email')->paginate(10);
```

<br />

It is always great to benefit from OOP. For instance we can create a custom attribute to check if a doctor has free slot or not.
```
class Doctor extend Eloquent 
{
    public function getHasFreeSlotAttribute()
    {
     // Business logic, this will return a boolean value
    }
}
```

<br />

* Let's see this attribute in action
```
$doctor = App\Models\Doctor::find(123);
if ( $doctor->hasFreeSlot ) {
    // Business logic, assign the appointment to the doctor etc
}
```

<br />

## Accessing Related Data
* Eager load related data 
In our typical Doctor appointment scenario, we have already defined relationships, this means we can access related data from the eloquent object. like
``` 
$doctor = App\Models\Doctor::where('email', 'john@domain.com')->first();
// Get appointments of this doctor
foreach( $doctor->appointments as $appointment ) {
   // Business log
}
```

<br />

Similarly, we can access doctor information from appointment model.
```
$appointment = App\Models\Apointment::find(1);
echo $appointment->doctor->name; // This will print doctor name of this appoinment
```

<br />

* However, while quering multiple records always eager load related data if required. Example
```
$doctors = App\Models\Doctor::select('id','name','email')
            ->with('apointments')
            ->paginate(10); // this will load doctors with appointments
```

<br />

## Returning JSON Data
All JSON response must have a signle pattern across the application. The will make our life easiter as well as the app afficient. Some times we need collection of data and some times a single row, in both cases the data must be consitent for each endpoint returning json data.
Luckily, Laravel providers all technigues required to mold the data for our needs. Let's check these examples.

#### Example 1, returning single row
Return a Doctor data
```
public function getDoctor($id)
{

    $doctor = App\Models\Doctor::select('id','email','phone','address','dob')
            ->with('appointments')
            ->find($id);
    return response->json($doctor);
}
```
This will return the following response
```
{
  "id": "123",
  "name" : "Joh Loop",
  "email" : "john@domain.com",
  "address" : "Bangkok Thailand",
  "apointments": [
    {
      "id" : "98",
      "date": "11-11-2019 16:10",
      "patient_id" : "678",
      "is_vip": false
    },
    {
      "id" : "99",
      "date": "11-11-2019 17:10",
      "patient_id" : "679",
      "is_vip": true
    }  
  ]
}
```
For some reasons, if we need to transform this data let's say we need another key "is_birthday", this will return true if the doctors date of birth matches today's date, also the other requirement is change the 'email' key into 'email_address'.
To acomplish this we must use Laravel [Eloquent Resources](https://laravel.com/docs/5.6/eloquent-resources)
* You can use tinker to create this resource
``` 
php artisan make:resource DoctorResource 
```
This will create the resource class. Now write the transformation logic in the toArray() function

```
class DoctorResource extends JsonResource 
{
    public functions toArray($request)
    {
        return [
            'name' => $request->name,
            'email_addres' => $request->email,
            'address' => $request->address,
            'is_birthday' => $request->dob ==  Carbon\Carbon::now() ? true : false,
            'apointments' => ApointmentResource::collection($request->apointments);
        ];
    }
}
```

Now, implement this resource
```
public function getDoctor($id)
{

    $doctor = App\Models\Doctor::select('id','email','phone','address','dob')
            ->with('appointments')
            ->find($id);
            
    return new DoctorResource($doctor);
}
```

<br />

This will return the following response.

```
{
  "id": "123",
  "name" : "Joh Loop",
  "email_address" : "john@domain.com",
  "address" : "Bangkok Thailand",
  "is_birthday" : false,
  "apointments": [
    {
      "id" : "98",
      "date": "11-11-2019 16:10",
      "patient_id" : "678",
      "is_vip": false
    },
    {
      "id" : "99",
      "date": "11-11-2019 17:10",
      "patient_id" : "679",
      "is_vip": true
    }  
  ]
}
```

<br />

#### Example 2, returning collection/paginated data
It is clear that, we always return paginated data for multiple records. We will never return multiple records with ->get(), this will load all data and it will eventually fail when data grows.
Laravel, eloquent or DB paginate() function on collection object will return what we need.
Let's see how the response looks like for returning all doctors.
```
public function getDoctors()
{
    return App\Models\Doctor::select('id','name','email')
          ->paginate(10);
}
```

This will return 

```
{
	"total": 200,
	"per_page": 2,
	"current_page": 1,
	"last_page": 20,
	"next_page_url": "http:\/\/localhost.com?page=2",
	"prev_page_url": null,
	"from": 1,
	"to": 2,
	"data": [{
		"id": "23",
	  	"name": "Martha Vjay",
	  	"email": "martha@domain.com
	}, {
		"id": "25",
	  	"name": "Skamful Khan",
	  	"email": "skamful@domain.com
	}]
}

```
This will perfectly work in our case, the actual data in the 'data' attribute. We also has all meta data like next page url, back page url, total records etc.
If we need to transform this data we will create Resource Collection as we did in the above example. For details about resource collection read [this](https://laravel.com/docs/5.6/eloquent-resources).

<br />

## PSR-2 Coding Style Standards
We need to follow the [PSR-2 PHP Coding](https://www.php-fig.org/psr/psr-2/). Laravel itself stictly follows these rules.
An example PSR-2 Compliant code https://github.com/shahidkarimi/documents/blob/master/MyClass.php
However some obvious mistakes we do are
1. Need to stick to a consitent variable naming convintion cameCase in our case.
2. Stating braces of functions and classes must start from a new line

