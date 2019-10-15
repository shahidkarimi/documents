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
## Query Against Eloquent in the Laravel way

Always use pagination for loading all data, unless needed for small size table.
```
Doctor::paginate(10);
```
This will return a Laravel Collection, you can convert this collection to any data structure supported by Laravel. (toJson(), toArray() etc).

It is always better to specify only required columns while quering against a table.
```
Doctor::select('id','name','phone','email')->paginate(10);
```
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
Let's see this attribute in action
```
$doctor = Doctor::find(123);
if ( $doctor->hasFreeSlot ) {
    // Business logic, assign the appointment to the doctor etc
}
```
