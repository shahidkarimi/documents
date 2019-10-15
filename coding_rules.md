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

### Doctor to Appointments relationship
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

### Appointments to Doctor relationship
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

### Accessing Related Data
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

Simlarly, we can access doctor information from appointment model.
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
This will create the resource class

```
class DoctorResource extends JsonResource 
{
    public functions toArray($request)
    {
        return [
            'name' => $request->name,
            'email_addres' => $requst->email,
            'address' => $request->address,
            'is_birthday' => $request->dob ==  Carbon\Carbon::now() ? true : false,
            'apointments' => ApointmentResource::collection($request->apointments);
        ];
    }
}
```

Now, implement this resouce
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
It is clear that, we always return paginated data for multiple records.
